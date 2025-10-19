<?php

namespace  App\Contracts;

interface PaymentGatewayInterface extends BaseInterface
{
    /**
    * process payment, return array with keys: success (bool), payment_id (string), response (array)
    */
    public function pay(array $payload): array;
}