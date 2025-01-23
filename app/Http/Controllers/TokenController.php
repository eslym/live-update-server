<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends Controller
{
    public function index(Request $request): Responsable
    {
        /** @var Client $client */
        $client = Client::first();

        $tokens = $client->tokens()->select(['id', 'name', 'expires_at', 'last_used_at', 'created_at'])->paginate();

        return inertia('token/index', [
            'tokens' => $tokens,
            'recentCreated' => $request->session()->get('recentCreated'),
        ]);
    }

    public function create(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        /** @var Client $client */
        $client = Client::first();

        $token = $client->createToken($data['name'], expiresAt: $data['expires_at'] ? Carbon::parse($data['expires_at']) : null);

        return redirect()->route('token.index')->with('recentCreated', $token->plainTextToken);
    }

    public function delete($tokenId): Response
    {
        Client::first()->tokens()->where('id', $tokenId)->delete();

        return redirect()->back()->with('alert', [
            'title' => 'Success',
            'content' => 'Token revoked successfully',
        ]);
    }
}
