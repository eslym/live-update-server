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
        $data = $request->validate([
            'otp' => ['required', 'string'],
        ]);

        if (!Authenticator::verifySession($data['otp'])) {
            return redirect()->back()
                ->with('alert', [
                    'title' => 'Invalid code',
                    'content' => 'The code you entered is invalid.',
                ]);
        }

        return redirect()->intended();
    }

    public function disable(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (RateLimiter::tooManyAttempts("disable-2fa:$user->id", 5)) {
            return back()->with(['alert' => [
                'title' => 'Too many attempts',
                'content' => 'Please try again later.',
            ]]);
        }

        RateLimiter::hit("disable-2fa:$user->id", 300);

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        RateLimiter::clear("disable-2fa:$user->id");

        $user->update([
            'google2fa_secret' => null,
        ]);

        $user->recovery_codes()->delete();

        return redirect()->route('profile')
            ->with('alert', [
                'title' => 'Success',
                'content' => 'Two-factor authentication has been disabled.',
            ]);
    }
}
