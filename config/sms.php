<?php

return [

    /*
    |--------------------------------------------------------------------
    | SMS Vendors
    |--------------------------------------------------------------------
    */
    'vendors' => [
        'gennet' => [
            'domain'    => env('GENNET_SMS_DOMAIN'),
            'api_token' => env('GENNET_SMS_API_TOKEN'),
        ],
        'automas' => [
            'base_url' => env('AUTOMAS_SMS_URL', 'https://api.automas.com.bd'),
            'api_key'  => env('AUTOMAS_SMS_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------
    | Allowed calling systems
    |--------------------------------------------------------------------
    | api_key => client name. Each system that's allowed to call this
    | microservice (institute-portal, back-office, anything future) gets
    | one key here. No DB — add a line + an .env var to onboard a new caller.
    */
    'clients' => [
        env('SMS_CLIENT_KEY_INSTITUTE_PORTAL') => 'institute-portal',
        env('SMS_CLIENT_KEY_BACKOFFICE')       => 'back-office',
    ],

];
