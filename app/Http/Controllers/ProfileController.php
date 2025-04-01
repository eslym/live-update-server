<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\UniqueRule;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function index(): Responsable
    {
        return inertia('profile/index', [
            'recovery_codes' => session()->get('recovery_codes'),
        ]);
    }

    public function update(Request $request): Response
    {
        if ($request->has('password')) {
            if (RateLimiter::tooManyAttempts('password:' . $request->user()->id, 5)) {
                return redirect()->back()
                    ->with('toast', [
                        'type' => 'error',
                        'title' => 'Too many attempts',
                        'description' => 'You have reached the maximum number of password verification attempts',
                    ])
                    ->withErrors([]);
            }
            RateLimiter::hit('password:' . $request->user()->id, 3600);
        }

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

        if ($request->has('password')) {
            RateLimiter::clear('password:' . $request->user()->id);
        }

        foreach (array_keys($data) as $key) {
            if ($data[$key] === null) {
                unset($data[$key]);
            }
        }

        /** @var User $user */
        $user = $request->user();

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Profile updated.',
        ]);
    }
}
