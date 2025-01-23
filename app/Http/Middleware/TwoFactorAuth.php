<?php

namespace App\Http\Middleware;

use App\Lib\Google2FA\Authenticator;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!($user instanceof User)) {
            return $next($request);
        }

        if ($user->google2fa_secret === null) {
            if (config('google2fa.enforce'))
                return redirect()->route('profile.2fa.setup');
            return $next($request);
        }

        if ($check = Authenticator::isExpired()) {
            session()->put('url.intended', $request->fullUrl());
            return redirect()->route('profile.2fa.verify')
                ->with('alert',
                    match ($check) {
                        Authenticator::EXPIRED => [
                            'title' => 'Two-factor authentication expired',
                            'content' => 'You need to verify your identity again in order to continue.',
                        ],
                        Authenticator::NO_SESSION => [
                            'title' => 'Two-factor authentication required',
                            'content' => 'You need to verify your identity in order to continue.',
                        ],
                        default => [],
                    }
                );
        }

        return $next($request);
    }
}
