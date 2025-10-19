<?php

namespace  App\Contracts;

use App\Models\CombinedOrder;

interface CombinedOrderInterface extends BaseInterface
{
    /**
     * @return CombinedOrder
     */
    public function getLastOrder(): CombinedOrder|null;
}
