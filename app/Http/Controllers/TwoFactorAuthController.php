<?php

namespace App\Http\Controllers;

use App\Lib\Google2FA\Authenticator;
use App\Models\User;
use App\Rules\TwoFactorCodeRule;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthController extends Controller
{
    public function setup(): Responsable
    {
        $secret = Authenticator::generateSecret();
        $qr = Authenticator::getQRCodeUrl(config('app.name'), auth()->user()->email, $secret);

        session()->flash('google2fa_secret', $secret);

        return inertia('2fa/setup', compact('secret', 'qr'));
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
            ->insert(array_map(fn($code) => ['code' => $code], $recovery_codes));

        session()->forget('google2fa_secret');

        return redirect()->route('profile')
            ->with('recovery_codes', $recovery_codes);
    }

    public function verify(Request $request): Response
    {
        $data = $request->validate([
            'otp' => ['required', 'string'],
        ]);

        if (!Authenticator::verifySession($data['otp'])) {
            return redirect()->back()
                ->with('alert', 'Invalid two-factor code.');
        }

        return redirect()->intended();
    }

    public function disable(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'google2fa_secret' => null,
        ]);

        $user->recovery_codes()->delete();

        return redirect()->route('profile.index');
    }
}
