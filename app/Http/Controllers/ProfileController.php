<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\UniqueRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function update(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => [
                'nullable', 'email',
                UniqueRule::make(User::class, 'email')
                    ->where('id', '!=', $request->user()->id)
            ],
            'current_password' => ['required_with:email,password', 'nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::default()],
        ]);

        foreach (array_keys($data) as $key) {
            if ($data[$key] === null) {
                unset($data[$key]);
            }
        }

        $request->user()->update($data);

        return redirect()->back()->with('alert', [
            'title' => 'Success',
            'content' => 'Password updated successfully.',
        ]);
    }
}
