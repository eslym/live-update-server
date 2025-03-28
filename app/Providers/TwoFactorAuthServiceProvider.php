<?php

namespace App\Providers;

use App\Lib\Google2FA\Authenticator;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->singleton(Google2FA::class, fn() => new Google2FA());

        $this->app->afterResolving(Google2FA::class, function (Google2FA $google2fa) {
            $google2fa->setAlgorithm(config('google2fa.algorithm'));
            $google2fa->setWindow(config('google2fa.window'));
        });

        $this->app->singleton(
            Authenticator::class,
            fn($app) => new Authenticator(
                $app->make(Google2FA::class),
                config('google2fa.secret_length'),
                config('google2fa.keep_alive'),
                config('google2fa.renew_time_frame')
            )
        );

        $this->app->alias(Authenticator::class, 'google2fa');

        $this->app->alias(Authenticator::class, '2fa');
    }
}
