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

        return inertia('2fa/setup', compact('secret', 'qr', 'debug_code'));
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
                'debug_code' => config('google2fa.debug') && config('app.debug')
                    ? Authenticator::getCurrentOTP(auth()->user()->google2fa_secret)
                    : null,
            ]
        );
    }

    public function verify(Request $request): Response
    {
        $unlockMode = $request->query->getString('mode') === 'unlock';

        $data = $request->validate([
            'otp' => ['required', 'string'],
        ]);

        if (!Authenticator::verifySession($data['otp'])) {
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
            return back()->with(['alert' => [
                'title' => 'Too many attempts',
                'content' => 'Please try again later.',
            ]]);
        }

        RateLimiter::hit("disable-2fa:$user->id", 300);

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
}
