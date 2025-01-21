<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

class ConfigController extends Controller
{
    public function json(): Response
    {
        $configs = [
            'APP_NAME' => config('app.name'),
            'ENFORCE_2FA' => config('google2fa.enforce'),
        ];
        return response()->json($configs);
    }
}
