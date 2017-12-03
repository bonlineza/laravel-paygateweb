<?php

    return [
        'secret'     => env('PAYGATE_SECRET', 'secret'),
        'id'         => env('PAYGATE_ID', '10011072130'), //test
        'currency'   => env('PAYGATE_CURRENCY', 'USD'),
        'locale'     => env('PAYGATE_LOCALE', 'en-za'),
        'return_url' => '',
        'notify_url' => '',
    ];