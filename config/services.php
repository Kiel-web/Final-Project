<?php
use Core\Env;

return [
    'cat_api' => [
        'url'       => rtrim((string) Env::get('CAT_API_URL', 'https://api.thecatapi.com/v1'), '/'),
        'key'       => (string) Env::get('CAT_API_KEY', ''),
        'ca_bundle' => Env::get('CAT_API_CA_BUNDLE') ?: null,
        'timeout'   => 5,

        'ttl_breeds' => 86400,
        'ttl_daily'  => 86400,

        'fallback_breeds' => [
            'abys' => 'Abyssinian', 'beng' => 'Bengal', 'birm' => 'Birman',
            'bsho' => 'British Shorthair', 'mcoo' => 'Maine Coon', 'pers' => 'Persian',
            'ragd' => 'Ragdoll', 'rblu' => 'Russian Blue', 'sfol' => 'Scottish Fold',
            'siam' => 'Siamese', 'sibe' => 'Siberian', 'sphy' => 'Sphynx',
        ],
    ],
];
