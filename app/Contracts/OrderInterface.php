<?php

namespace  App\Contracts;

use App\Models\Order;

interface OrderInterface extends BaseInterface
{
    /**
     * @return Order
     */
    public function getLastOrder(): Order|null;
}
