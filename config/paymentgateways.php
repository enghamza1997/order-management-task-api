<?php
return [
    'drivers' => [
        'paymob' => App\Gateways\PaymobGateway::class,
        'paypal' => App\Gateways\PaypalGateway::class,
    ],
];