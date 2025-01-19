<?php

use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;

Route::get('/bundles/{project}', [APIController::class, 'version']);
