<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
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

    Route::post('/projects', [ProjectController::class, 'create'])->name('project.create');

    Route::get('/projects/{project}', [ProjectController::class, 'view'])->name('project.view');
    Route::post('/projects/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'delete'])->name('project.delete');

    Route::post('/projects/{project}/versions', [VersionController::class, 'create'])->name('version.create');
    Route::post('/projects/{project}/versions/{version}', [VersionController::class, 'update'])->name('version.update');
    Route::delete('/projects/{project}/versions/{version}', [VersionController::class, 'delete'])->name('version.delete');

    Route::inertia('/profile', 'profile/index')->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/tokens', [TokenController::class, 'index'])->name('token.index');
    Route::post('/tokens', [TokenController::class, 'create'])->name('token.create');
    Route::delete('/tokens/{tokenId}', [TokenController::class, 'delete'])->name('token.delete');
});
