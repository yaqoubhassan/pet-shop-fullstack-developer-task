<?php

return [
    'secret' => env('JWT_SECRET'),
    'keys' => [
        'private' => base_path(env('JWT_PRIVATE_KEY_PATH')),
        'public' => base_path(env('JWT_PUBLIC_KEY_PATH')),
    ],
    'issuer' => env('JWT_ISSUER', env('APP_URL')),
];
