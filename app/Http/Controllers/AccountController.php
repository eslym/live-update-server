<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    public function update(Request $request): Response
    {
        $data = $request->validate([
            'current_password' => ['required', 'password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return redirect()->back()->with('alert', 'Password updated!');
    }
}
