<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\VersionController;
use App\Http\Middleware\MustHave2FA;
use App\Http\Middleware\MustNotHave2FA;
use App\Http\Middleware\NoUserExists;
use App\Http\Middleware\TwoFactorAuth;
use Illuminate\Support\Facades\Route;

Route::get('/config.json', [ConfigController::class, 'json'])
    ->name('config.json');

Route::middleware(['guest'])->group(function () {
    Route::inertia('/login', 'login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware([NoUserExists::class])->group(function () {
    Route::inertia('/setup', 'setup')->name('setup');
    Route::post('/setup', [SetupController::class, 'setup']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::middleware([TwoFactorAuth::class])->group(function () {
        Route::get('/', [ProjectController::class, 'index'])
            ->name('project.index');

        Route::post('/projects', [ProjectController::class, 'create'])
            ->name('project.create');

        Route::get('/projects/{project}', [ProjectController::class, 'view'])
            ->name('project.view');
        Route::post('/projects/{project}', [ProjectController::class, 'update'])
            ->name('project.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'delete'])
            ->name('project.delete');

        Route::post('/projects/{project}/versions', [VersionController::class, 'create'])
            ->name('version.create');
        Route::post('/projects/{project}/versions/{version}', [VersionController::class, 'update'])
            ->name('version.update');
        Route::delete('/projects/{project}/versions/{version}', [VersionController::class, 'delete'])
            ->name('version.delete');

        Route::inertia('/profile', 'profile/index')
            ->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/tokens', [TokenController::class, 'index'])
            ->name('token.index');
        Route::post('/tokens', [TokenController::class, 'create'])
            ->name('token.create');
        Route::delete('/tokens/{tokenId}', [TokenController::class, 'delete'])
            ->name('token.delete');
    });

    Route::middleware([MustNotHave2FA::class])->group(function () {
        Route::get('/2fa/setup', [TwoFactorAuthController::class, 'setup'])
            ->name('profile.2fa.setup');
        Route::post('/2fa/setup', [TwoFactorAuthController::class, 'store']);
    });

    Route::middleware([MustHave2FA::class])->group(function () {
        Route::inertia('/2fa', '/2fa/verify')
            ->name('profile.2fa.verify');
        Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verify']);
    });
});
