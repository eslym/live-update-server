<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\VersionController;
use App\Http\Middleware\NoUserExists;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::inertia('/login', 'login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware([NoUserExists::class])->group(function () {
    Route::inertia('/setup', 'setup')->name('setup');
    Route::post('/setup', [SetupController::class, 'setup']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('project.index');
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::post('/create', [ProjectController::class, 'create'])->name('project.create');

    Route::get('/projects/{project}', [ProjectController::class, 'view'])->name('project.view');
    Route::post('/projects/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'delete'])->name('project.delete');

    Route::post('/projects/{project}/{version}', [VersionController::class, 'update'])->name('version.update');
    Route::delete('/projects/{project}/{version}', [VersionController::class, 'delete'])->name('version.delete');

    Route::inertia('/account', 'account/index')->name('account');
    Route::post('/account', [AccountController::class, 'update'])->name('account.update');

    Route::get('/tokens', [TokenController::class, 'index'])->name('token.index');
});
