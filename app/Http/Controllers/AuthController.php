<?php

namespace App\Http\Controllers;

use App\Lib\Google2FA\Authenticator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): Response
    {
        if (RateLimiter::tooManyAttempts('login:' . $request->ip(), 5)) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Too many attempts',
                    'description' => 'You have reached the maximum number of login attempts',
                ])
                ->withErrors([]);
        }

        RateLimiter::hit('login:' . $request->ip(), 3600);

        $validator = Validator::make($request->input(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $validator->validate();

        if (auth()->attempt($credentials)) {
            RateLimiter::clear('login:' . $request->ip());

            /** @var User $user */
            $user = auth()->user();

            $res = $user->google2fa_secret ?
                redirect()->route('profile.2fa.verify') :
                redirect()->intended();

            return $res->with('toast', [
                'type' => 'success',
                'title' => 'You are logged in',
            ]);
        }

        $validator->errors()->add('email', 'Invalid credentials');

        throw new ValidationException($validator);
    }

    public function logout(): Response
    {
        auth()->logout();
        Authenticator::clearSession();

        return redirect()->to('/login')->with('toast', [
            'type' => 'success',
            'title' => 'You have been logged out'
        ]);
    }
}
