<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends Controller
{
    public function index(Request $request): Responsable
    {
        /** @var User $user */
        $user = $request->user();

        $tokens = $user->tokens()->select(['id', 'name', 'expires_at', 'last_used_at', 'created_at'])->paginate();

        return inertia('token/index', [
            'tokens' => $tokens,
            'newToken' => $request->session()->get('newToken'),
        ]);
    }

    public function create(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $token = $user->createToken($data['name'], expiresAt: $data['expires_at']);

        return redirect()->route('token.index')->with('newToken', $token->plainTextToken);
    }

    public function delete(Request $request, $tokenId): Response
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('token.index')->with('alert', 'Token deleted!');
    }
}
