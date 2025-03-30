<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Rules\UniqueRule;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    public function index(Request $request): Responsable
    {
        /** @var User $user */
        $user = $request->user();

        /** @var LengthAwarePaginator $accounts */
        $accounts = User::query()
            ->select([
                'id',
                'nanoid',
                'name',
                'email',
                'is_superadmin',
                'google2fa_secret',
                'created_at',
            ])
            ->paginate(15);

        $can_create = $user->is_superadmin;

        return inertia('account/index', [
            'accounts' => UserResource::collection($accounts),
            'can_create' => $can_create,
        ]);
    }

    public function create(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                UniqueRule::make(User::class, 'email')
            ],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!$user->is_superadmin) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You do not have permission to create accounts.',
                ]);
        }

        User::create($data);


        return redirect()->back()
            ->with('toast', [
                'type' => 'success',
                'title' => 'Account created successfully',
            ]);
    }

    public function update(Request $request, User $account): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                UniqueRule::make(User::class, 'email')
                    ->where('id', '!=', $account->id)
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'remove_2fa' => ['nullable', 'boolean'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!$user->is_superadmin) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You do not have permission to update accounts.',
                ]);
        }

        if ($user->id === $account->id) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You cannot update your own account, please use the profile settings.',
                ]);
        }

        if ($account->is_superadmin) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You cannot update this account.',
                ]);
        }

        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }

        if (isset($data['email']) && $data['email'] !== $account->email) {
            $data['email_verified_at'] = null;
        }

        if ($data['remove_2fa'] ?? false) {
            $data['google2fa_secret'] = null;
        }

        $account->update($data);

        return redirect()->back()
            ->with('toast', [
                'type' => 'success',
                'title' => 'Account updated successfully',
            ]);
    }

    public function delete(User $account): Response
    {
        /** @var User $user */
        $user = request()->user();

        if (!$user->is_superadmin) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You do not have permission to delete accounts.',
                ]);
        }

        if ($user->id === $account->id) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'description' => 'You cannot delete your own account.',
                ]);
        }

        if ($account->is_superadmin) {
            return redirect()->back()
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'You cannot delete this account.',
                ]);
        }

        $account->delete();

        return redirect()->back()
            ->with('alert', [
                'toast' => 'success',
                'title' => 'Account deleted successfully',
            ]);
    }
}
