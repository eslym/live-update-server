<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class SetupController extends Controller
{
    public function setup(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ]);

        User::create([
            ...$data,
            'is_superadmin' => true,
        ]);

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'title' => 'Setup Complete',
            'description' => 'You can now log in with the credentials you just created.',
        ]);
    }
}
