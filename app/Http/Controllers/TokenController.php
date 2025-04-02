<?php

namespace App\Http\Controllers;

use App\Http\Resources\TokenResource;
use App\Lib\Utils;
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

        $query = $client->tokens()
            ->select(['id', 'name', 'expires_at', 'last_used_at', 'created_at']);

        switch ($use = $request->query->getString('use')) {
            case 'used':
                $query->whereNotNull('last_used_at');
                break;
            case 'unused':
                $query->whereNull('last_used_at');
                break;
        }

        switch ($exp = $request->query->getString('exp')) {
            case 'expired':
                $query->where('expires_at', '<', Carbon::now());
                break;
            case 'active':
                $query->where(
                    fn($query) => $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', Carbon::now())
                );
                break;
            case 'permanent':
                $query->whereNull('expires_at');
                break;
        }

        if ($search = $request->query->getString('q')) {
            $keywords = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', $keyword);
                }
            });
        }

        Utils::makeSort($query, ['name', 'expires_at', 'last_used_at', 'created_at']);

        return inertia('token/index', [
            'title' => 'API Tokens',
            'tokens' => fn() => TokenResource::collection($query->paginate(20))
                ->additional([
                    'meta' => [
                        'params' => [
                            'use' => $use,
                            'exp' => $exp,
                            'q' => $search,
                            'sort' => $request->query->getString('sort'),
                        ]
                    ]
                ]),
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

        if ($data['expires_at']) {
            $data['expires_at'] = Carbon::parse($data['expires_at'], Utils::getClientTimezone())
                ->timezone(config('app.timezone'));
        }

        $token = $client->createToken(
            $data['name'],
            expiresAt: $data['expires_at']
        );

        return redirect()->route('token.index')->with('recentCreated', $token->plainTextToken);
    }

    public function delete($tokenId): Response
    {
        Client::first()->tokens()->where('id', $tokenId)->delete();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Token revoked successfully',
        ]);
    }
}
