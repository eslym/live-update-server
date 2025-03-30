<?php

namespace App\Http\Controllers;

use App\Lib\Google2FA\Authenticator;
use App\Models\User;
use App\Rules\TwoFactorCodeRule;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthController extends Controller
{
    public function setup(): Responsable
    {
        if (session()->has('google2fa_secret')) {
            $secret = session()->get('google2fa_secret');
        } else {
            session()->put('google2fa_secret', $secret = Authenticator::generateSecret());
        }

        $qr = Authenticator::getQRCodeUrl(config('app.name'), auth()->user()->email, $secret);

        $debug_code = config('google2fa.debug') && config('app.debug')
            ? Authenticator::getCurrentOTP($secret)
            : null;

        return inertia('2fa/setup', [
            ...compact('secret', 'qr', 'debug_code'),
            'title' => 'Setup 2FA',
            'breadcrumbs' => [
                ['label' => '2FA'],
                ['label' => 'Setup', 'href' => route('profile.2fa.setup')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!session()->has('google2fa_secret')) {
            return redirect()->route('profile.2fa.setup');
        }

        $secret = session()->get('google2fa_secret');

        $request->validate([
            'otp' => ['required', 'digits:6', new TwoFactorCodeRule($secret)],
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->update([
            'google2fa_secret' => $secret,
        ]);

        $recovery_codes = Authenticator::generateRecoveryCodes();

        $user->recovery_codes()
            ->delete();

        $user->recovery_codes()
            ->insert(array_map(fn($code) => ['code' => $code, 'user_id' => $user->id], $recovery_codes));

        session()->forget('google2fa_secret');

        Authenticator::forceSession();

        return redirect()->route('profile')
            ->with('recovery_codes', $recovery_codes);
    }

    public function verifyPage(): Responsable
    {
        return inertia('2fa/verify',
            [
                'title' => 'Verify 2FA',
                'breadcrumbs' => [
                    ['label' => '2FA'],
                    ['label' => 'Verify', 'href' => route('profile.2fa.verify')],
                ],
                'debug_code' => config('google2fa.debug') && config('app.debug')
                    ? Authenticator::getCurrentOTP(auth()->user()->google2fa_secret)
                    : null,
            ]
        );
    }

    public function verify(Request $request): Response
    {
        $unlockMode = $request->query->getString('mode') === 'unlock';

        if (RateLimiter::tooManyAttempts("2fa-verify:" . $request->user()->id, 5)) {
            if ($unlockMode) {
                return response()->json([
                    'message' => 'Too many attempts',
                ], 429);
            }
            return back()->with(['toast' => [
                'type' => 'error',
                'title' => 'Too many attempts',
                'description' => 'You have reached the maximum number of verification attempts',
            ]])
                ->withErrors([]);
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'in:otp,recovery'],
            'otp' => ['required_if:type,otp', 'nullable', 'string', 'digits:6'],
            'code' => ['required_if:type,recovery', 'nullable', 'string', 'digits:9'],
        ]);

        if ($data['type'] === 'otp') {
            if (!Authenticator::verifySession($data['otp'])) {
                RateLimiter::hit("2fa-verify:" . $request->user()->id, 300);
                return $this->invalid($unlockMode);
            }
        } else {
            /** @var User $user */
            $user = $request->user();
            $code = preg_replace(
                '/^(\d{3})(\d{3})(\d{3})$/',
                '$1-$2-$3',
                $data['code']
            );
            $recovery_code = $user->recovery_codes()
                ->where('code', $code)
                ->first();
            if (!$recovery_code) {
                RateLimiter::hit("2fa-verify:" . $request->user()->id, 300);
                return $this->invalid($unlockMode);
            }

            $recovery_code->delete();
        }

        RateLimiter::clear("2fa-verify:" . $request->user()->id);

        if ($unlockMode) {
            return response()->json([
                'message' => 'Unlocked',
            ]);
        }
        return redirect()->intended();
    }

    public function disable(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (RateLimiter::tooManyAttempts("password:$user->id", 5)) {
            return back()->with(['toast' => [
                'type' => 'error',
                'title' => 'Too many attempts',
                'description' => 'You have reached the maximum number of password verification attempts',
            ]])
                ->withErrors([]);
        }

        RateLimiter::hit("password:$user->id", 300);

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        RateLimiter::clear("password:$user->id");

        $user->update([
            'google2fa_secret' => null,
        ]);

        return redirect()->route('profile')
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Two-factor authentication has been disabled.',
            ]);
    }

    public function check(Request $request): Response
    {
        $user = $request->user();

        if (!$user->google2fa_secret) {
            return response()->json([
                'should_renew' => false,
                'message' => 'Two-factor authentication is not enabled.',
            ]);
        }

        $check = Authenticator::shouldRenew();

        $debug_code = null;
        if ($check !== 0 && config('google2fa.debug') && config('app.debug')) {
            $debug_code = Authenticator::getCurrentOTP($user->google2fa_secret);
        }

        return response()->json([
            'should_renew' => $check !== 0,
            'message' => match ($check) {
                Authenticator::EXPIRED => 'Two-factor authentication expired',
                Authenticator::NO_SESSION => 'Two-factor authentication required',
                default => 'Session is valid.',
            },
            'debug_code' => $debug_code,
        ]);
    }

    private function invalid(bool $unlockMode): Response
    {
        if ($unlockMode) {
            return response()->json([
                'message' => 'Invalid code',
            ], 401);
        }

        return redirect()->back()
            ->with('alert', [
                'title' => 'Invalid code',
                'content' => 'The code you entered is invalid.',
            ]);
    }
}
