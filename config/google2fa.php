<?php

use PragmaRX\Google2FA\Support\Constants;

return [
    'algorithm' => Constants::SHA1,

    'secret_length' => env('GOOGLE2FA_SECRET_LENGTH', 16),

    'window' => env('GOOGLE2FA_WINDOW', 1),

    /**
     * How long to keep the authenticated session alive, in minutes.
     */
    'keep_alive' => env('GOOGLE2FA_KEEP_ALIVE', 600),

    'debug' => (bool)env('GOOGLE2FA_DEBUG', false),

    'enforce' => (bool)env('GOOGLE2FA_ENFORCE', false),
];
