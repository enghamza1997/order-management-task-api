<?php

namespace App\Services;

use App\Contracts\OrderPackageInterface;
use App\Models\OrderPackage;

class OrderService
{
    private OrderPackageInterface $orderPackageRepository;

    public function __construct(OrderPackageInterface $orderPackageRepository)
    {
        $this->orderPackageRepository = $orderPackageRepository;
    }

    /**
    * function to confirm combined order
    * @param App\Models\CombinedOrder $combined_order
    * @return bool
    */
    public function confirmCombinedOrder($combined_order)
    {
        // get all pending sub orders
        $orders = $combined_order->orders;

        if (!$orders) {
            return ['done' => false, 'message' => __('there\'s no orders to confirm.')];
        }
        $responses = [];

        foreach ($orders as $order) {
            $responses += $this->confirmOrder($order);
        }

        return $responses;
    }

    /**
     * function to confirm the seller sub order
     * @param \App\Models\Order|int $order
     * @return array
     */
    public function confirmOrder($order)
    {
        // get all pending sub orders
        $orderPackages = $order->orderPackages;

        if (!$orderPackages) {
            return ['done' => false, 'message' => __('there\'s no order packages to confirm.')];
        }
        $responses = [];

        foreach ($orderPackages as $package) {
            $responses += $this->confirmOrderPackage($package, true);
        }

        return $responses;
    }

    /**
     * function to confirm order package
     *  @param \App\Models\OrderPackage|int  $orderPackage
     */
    public function confirmOrderPackage($package, $initialConfirm = true)
    {
        $package = $package instanceof OrderPackage ? $package : $this->orderPackageRepository->find($package, ['packageItems' => function ($query) {
            return $query->where('item_status', 'pending');
        }]);

        $response = []; // her the carrier shipping response

        if (isset($response['done']) && $response['done']) {
            $this->updateOrderPackage($package, $response['data']) &&
            $initialConfirm && $this->createInitialPackageTrack($package) && $this->setPackageItemsState($package);
            return [];
        } else {
            $this->updateOrderPackage($package, $response['error']);
            return [$package->id => $response['error']['error_message']];
        }
    }


    /**
    * function to generate order code
    * @return bool
    */
    public function generateOrderCode()
    {
        $now = date("Ymd");
        $rand = sprintf("%04d", rand(0,9999));
        return $now . $rand;
    }
}
