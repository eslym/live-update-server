<?php

use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;

Route::get('/bundles/{project}', [APIController::class, 'query'])
    ->name('api.query');

Route::get('/bundles/{project}/{version}.zip', [APIController::class, 'download'])
    ->name('api.download');

Route::post('/bundles/{project}', [APIController::class, 'create'])
    ->middleware(['auth:sanctum'])
    ->name('api.create');
