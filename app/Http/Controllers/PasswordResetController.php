<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function request(Request $request): Response
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (RateLimiter::tooManyAttempts($rl_key = 'reset-password:' . $data['email'], 1)) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Too many requests',
                    'description' => 'Please wait a few minutes before trying again.',
                ])
                ->withErrors([]);
        }
        RateLimiter::hit($rl_key, 150);

        if (RateLimiter::tooManyAttempts($rl_key = 'reset-password:' . $request->ip(), 15)) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Too many requests',
                    'description' => 'Please wait a few minutes before trying again.',
                ])
                ->withErrors([]);
        }
        RateLimiter::hit($rl_key, 3600);

        /** @var User $user */
        $user = User::where($data)->first();

        if (!$user) {
            return redirect()->back()
                ->with('alert', [
                    'title' => 'Email Sent',
                    'content' => 'If the email you entered is associated with an account, a password reset link has been sent to it.',
                ]);
        }

        $user->sendPasswordResetNotification(Password::createToken($user));

        return redirect()->back()
            ->with('alert', [
                'title' => 'Email Sent',
                'content' => 'If the email you entered is associated with an account, a password reset link has been sent to it.',
            ]);
    }

    public function reset(Request $request, string $token): Response
    {
        $data = $request->validate([
            'password' => ['required', 'string', PasswordRule::default(), 'confirmed'],
        ]);

        /** @var User $user */
        $user = User::where('email', $request->query->getString('email'))->first();

        if (!$user || !Password::tokenExists($user, $token)) {
            return redirect()->route('login')
                ->with('alert', [
                    'title' => 'Expired',
                    'content' => 'The password reset link has expired, please request a new one.',
                ]);
        }

        $user->password = $data['password'];
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }
        $user->save();

        Password::deleteToken($user);

        return redirect()->route('login')
            ->with('alert', [
                'title' => 'Password Reset',
                'content' => 'Your password has been reset.',
            ]);
    }
}
