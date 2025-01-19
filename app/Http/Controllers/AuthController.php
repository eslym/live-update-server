<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $validator = Validator::make($request->input(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $validator->validate();

        if (auth()->attempt($credentials)) {
            return redirect()->to('/');
        }

        $validator->errors()->add('email', 'Invalid credentials');

        throw new ValidationException($validator);
    }

    public function logout(): Response
    {
        auth()->logout();

        return redirect()->to('/login')->with('alert', 'You have been logged out');
    }
}
