<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        if (file_exists($path = public_path('build/manifest.json'))) {
            return md5_file($path);
        }
        return null;
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user' => fn() => $request->user()
                ? [
                    ...$request->user()->only('id', 'nanoid', 'name', 'email'),
                    '2fa_enabled' => $request->user()->google2fa_secret !== null,
                ]
                : null,
            'route' => fn() => $request->route()->getName(),
            'alert' => fn() => $request->session()->get('alert'),
        ]);
    }
}
