<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Model Paths
    |--------------------------------------------------------------------------
    |
    | List of paths containing models needed to be synchronized.
    | Each path is the key and value must be the namespace of the classes.
    | Relative paths are scanned in respect to project root.
    |
    */

    'paths' => [
        'app/Models' => 'App\\Models',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Tokens
    |--------------------------------------------------------------------------
    |
    | API key signing tenant tokens, which must allow searching the indexes
    | the tokens give access to. The master key can't sign tenant tokens.
    | Setting the uid of the key saves looking it up for every token.
    |
    */

    'tenant_token' => [
        'key'     => env('MEILITOOLS_TENANT_TOKEN_KEY'),
        'key_uid' => env('MEILITOOLS_TENANT_TOKEN_KEY_UID'),
    ],
];
