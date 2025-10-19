<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\CombinedOrderInterface;
use App\Contracts\OrderInterface;
use App\Contracts\OrderPackageInterface;
use App\Contracts\PackageItemInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use App\Http\Requests\Api\Order\StoreOrderRequest;
use App\Http\Requests\Api\Order\UpdateOrderRequest;
use App\Http\Resources\CombinedOrderResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    private CombinedOrderInterface $combinedOrderRepository;
    private OrderInterface $orderRepository;
    private OrderPackageInterface $orderPackageRepository;
    private PackageItemInterface $packageItemRepository;
    private OrderService $orderService;

    public function __construct(
        CombinedOrderInterface $combinedOrderRepository,
        OrderInterface $orderRepository,
        OrderPackageInterface $orderPackageRepository,
        PackageItemInterface $packageItemRepository,
        OrderService $orderService,
    ) {
        $this->combinedOrderRepository = $combinedOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->orderPackageRepository = $orderPackageRepository;
        $this->packageItemRepository = $packageItemRepository;
        $this->orderService = $orderService;
    }

    /**
     * GET /admin/orders
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $status = $request->query('status');
        $relations = ['orders', 'orders.orderPackages.packageItems', 'orders.seller'];

        $queryOptions = [];
        if ($status) {
            $queryOptions['status'] = $status;
        }

        $combined_orders = $this->combinedOrderRepository->paginate('id', $relations, $queryOptions, $perPage);
        return $this->success_response(CombinedOrderResource::collection($combined_orders), 'Orders Collection');
    }


    /**
     * POST orders
     */
    public function store(StoreOrderRequest $request)
    {
        $items = $request->input('items');

        $user = User::firstOrCreate(
            [
                'email' => $request->customer_email,
            ],
            [
                'name' => $request->customer_name ?? 'Guest User',
                'password' => bcrypt(str()->random(12)),
            ]
        );

        DB::beginTransaction();
        try {
            $address = [
                'street' => '123 Test Street',
                'city' => 'Cairo',
                'country' => 'Egypt',
            ];

            $subTotal = collect($items)->sum(fn($it) => $it['price'] * $it['quantity']);
            $shipmentFees = 0;
            $commission = 0;
            $discount = 0;
            $totalAmount = $subTotal + $shipmentFees - $discount;

            // 1. Create Combined Order
            $combinedOrderData = [
                'user_id' => $user->id,
                'order_code' => $this->orderService->generateOrderCode(),
                'address' => $address,
                'sub_total' => $subTotal,
                'shipment_fees' => $shipmentFees,
                'commission_amount' => $commission,
                'tax' => 0,
                'payment_fees' => 0,
                'coupon_discount' => 0,
                'seller_discount' => $discount,
                'total_amount' => $totalAmount,
                'items_count' => count($items),
                'order_status' => OrderStatus::PENDING,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => 'unpaid',
            ];

            $combinedOrder = $this->combinedOrderRepository->store($combinedOrderData);

            // 2. Create Order
            $orderData = [
                'combined_order_id' => $combinedOrder->id,
                'seller_id' => $user->id,
                'order_code' => $this->orderService->generateOrderCode(),
                'sub_total' => $subTotal,
                'shipment_fees' => $shipmentFees,
                'commission_amount' => $commission,
                'tax' => 0,
                'seller_discount' => $discount,
                'total_amount' => $totalAmount,
                'items_count' => count($items),
                'order_status' => OrderStatus::PENDING,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => 'unpaid',
            ];

            $order = $this->orderRepository->store($orderData);

            // 3. Create Order Package
            $orderPackageData = [
                'order_id' => $order->id,
                'pickup_address' => $address,
                'sub_total' => $subTotal,
                'shipment_fees' => $shipmentFees,
                'total_amount' => $totalAmount,
                'tracking_number' => 'TRK-' . strtoupper(uniqid()),
                'package_details' => [
                    'package_code' => 'PKG-' . strtoupper(uniqid()),
                    'items_summary' => count($items) . ' items',
                ],
                'cod' => false,
                'shipment_message' => null,
                'shipment_status' => 'pending',
                'package_status' => 'pending',
                'delivery_date' => null,
                'fullfilled' => false,
                'items_count' => count($items),
            ];

            $orderPackage = $this->orderPackageRepository->store($orderPackageData);

            // 4. Loop items and create catalog, item, listing
            foreach ($items as $it) {

                // Step 4.1: Create or find Catalog
                $catalog = \App\Models\Catalog::firstOrCreate(
                    [
                        'item_name_' . SL => $it['product_name'],
                    ],
                    [
                        'item_name_' . FL => $it['product_name'],
                        'description_' . SL => 'Auto-generated for order',
                        'description_' . FL => 'Auto-generated for order',
                        'catalog_sku' => strtoupper('CAT-' . uniqid()),
                        'tags' => $it['product_name'],
                        'seo_keywords' => $it['product_name'],
                        'item_slug' => str_replace(' ', '_', strtolower($it['product_name'])),
                        'catalog_type' => 'single',
                        'created_by' => $user->id,
                    ]
                );

                // Step 4.2: Create or find Catalog Item
                $catalogItem = \App\Models\CatalogItem::firstOrCreate(
                    [
                        'catalog_id' => $catalog->id,
                        'item_title_' . SL => $it['product_name'],
                    ],
                    [
                        'item_title_' . FL => $it['product_name'],
                        'item_sku' => strtoupper('SKU-' . uniqid()),
                        'created_by' => $user->id,
                    ]
                );

                // Step 4.3: Create or find Listed Item
                $listedItem = \App\Models\ListedItem::firstOrCreate(
                    [
                        'catalog_item_id' => $catalogItem->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'warehouse_id' => null,
                        'price' => $it['price'],
                        'quantity' => $it['quantity'],
                        'internal_sku' => strtoupper('INT-' . uniqid()),
                        'listed_sku' => strtoupper('LST-' . uniqid()),
                        'published' => true,
                    ]
                );

                // Step 4.4: Create package item linked to listed item
                $packageItemData = [
                    'package_id' => $orderPackage->id,
                    'item_id' => $listedItem->id,
                    'warehouse_id' => null,
                    'commission_amount' => 0,
                    'quantity' => $it['quantity'],
                    'price' => $it['price'],
                    'item_status' => 'pending',
                ];

                $this->packageItemRepository->store($packageItemData);
            }

            DB::commit();

            $order->load('orderPackages.packageItems');

            return $this->success_response(new CombinedOrderResource($combinedOrder), 'Order created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error_response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update(UpdateOrderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $order = $this->orderRepository->find($id);

            if (!$order) {
                return $this->error_response('Order not found', Response::HTTP_NOT_FOUND);
            }

            $combinedOrder = $order->combinedOrder;
            $orderPackage = $order->orderPackages()->first();

            $items = $request->input('items', []);
            $user = $order->user;

            // Recalculate amounts
            $subTotal = collect($items)->sum(fn($it) => $it['price'] * $it['quantity']);
            $shipmentFees = $request->input('shipment_fees', $order->shipment_fees ?? 0);
            $commission = $request->input('commission_amount', $order->commission_amount ?? 0);
            $discount = $request->input('discount', $order->seller_discount ?? 0);
            $totalAmount = $subTotal + $shipmentFees - $discount;

            // 1. Update Combined Order
            $combinedOrder->update([
                'sub_total' => $subTotal,
                'shipment_fees' => $shipmentFees,
                'commission_amount' => $commission,
                'seller_discount' => $discount,
                'total_amount' => $totalAmount,
                'items_count' => count($items),
                'payment_method' => $request->input('payment_method', $combinedOrder->payment_method),
                'payment_status' => $request->input('payment_status', $combinedOrder->payment_status),
                'order_status' => $request->input('order_status', $combinedOrder->order_status),
            ]);

            // 2. Update Order
            $order->update([
                'sub_total' => $subTotal,
                'shipment_fees' => $shipmentFees,
                'commission_amount' => $commission,
                'seller_discount' => $discount,
                'total_amount' => $totalAmount,
                'items_count' => count($items),
                'payment_method' => $request->input('payment_method', $order->payment_method),
                'payment_status' => $request->input('payment_status', $order->payment_status),
                'order_status' => $request->input('order_status', $order->order_status),
            ]);

            // 3. Update Order Package
            if ($orderPackage) {
                $orderPackage->update([
                    'sub_total' => $subTotal,
                    'shipment_fees' => $shipmentFees,
                    'total_amount' => $totalAmount,
                    'items_count' => count($items),
                    'shipment_status' => $request->input('shipment_status', $orderPackage->shipment_status),
                    'package_status' => $request->input('package_status', $orderPackage->package_status),
                ]);

                // Delete old package items
                $orderPackage->packageItems()->delete();

                // Recreate package items
                foreach ($items as $it) {
                    // Update or create Catalog
                    $catalog = \App\Models\Catalog::firstOrCreate(
                        [
                            'item_name_' . SL => $it['product_name'],
                        ],
                        [
                            'item_name_' . FL => $it['product_name'],
                            'description_' . SL => 'Auto-generated for order update',
                            'description_' . FL => 'Auto-generated for order update',
                            'catalog_sku' => strtoupper('CAT-' . uniqid()),
                            'tags' => $it['product_name'],
                            'seo_keywords' => $it['product_name'],
                            'item_slug' => \Illuminate\Support\Str::slug($it['product_name'], '_'),
                            'catalog_type' => 'single',
                            'created_by' => $user->id,
                        ]
                    );

                    // Update or create Catalog Item
                    $catalogItem = \App\Models\CatalogItem::firstOrCreate(
                        [
                            'catalog_id' => $catalog->id,
                            'item_title_' . SL => $it['product_name'],
                        ],
                        [
                            'item_title_' . FL => $it['product_name'],
                            'item_sku' => strtoupper('SKU-' . uniqid()),
                            'created_by' => $user->id,
                        ]
                    );

                    // Update or create Listed Item
                    $listedItem = \App\Models\ListedItem::updateOrCreate(
                        [
                            'catalog_item_id' => $catalogItem->id,
                            'user_id' => $user->id,
                        ],
                        [
                            'price' => $it['price'],
                            'quantity' => $it['quantity'],
                            'published' => true,
                        ]
                    );

                    // Recreate package items
                    $this->packageItemRepository->store([
                        'package_id' => $orderPackage->id,
                        'item_id' => $listedItem->id,
                        'warehouse_id' => null,
                        'commission_amount' => 0,
                        'quantity' => $it['quantity'],
                        'price' => $it['price'],
                        'item_status' => 'pending',
                    ]);
                }
            }

            DB::commit();

            $order->load('orderPackages.packageItems');

            return $this->success_response(new CombinedOrderResource($combinedOrder), 'Order updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error_response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // 1. Find the combined order
            $combinedOrder = $this->combinedOrderRepository->find($id);
            if (!$combinedOrder) {
                return $this->error_response('Order not found.', Response::HTTP_NOT_FOUND);
            }

            // 2. Check if the order has any payments
            // Assuming relation: $combinedOrder->payments() OR via paymentRepository
            $hasPayments = $combinedOrder->payments()->exists();

            if ($hasPayments) {
                return $this->error_response(
                    'Cannot delete order with associated payments.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // 3. Cascade delete related data (orders, packages, items)
            foreach ($combinedOrder->orders as $order) {
                foreach ($order->orderPackages as $package) {
                    $package->packageItems()->delete();
                    $this->orderPackageRepository->destroy($package->id);
                }

                $this->orderRepository->destroy($order->id);
            }

            // 4. Finally, delete the combined order
            $this->combinedOrderRepository->destroy($combinedOrder->id);

            DB::commit();

            return $this->success_response([], 'Order deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error_response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
    * function to confirm the combined order
    * @param int|string $order
    */
    public function confirmCombinedOrder(int|string $order)
    {
        $combined_order = $this->combinedOrderRepository->find($order,
        ['orders' => function ($query) {
            return $query->where('order_status', OrderStatus::PENDING);
        },
        'orders.orderPackages' => function ($query) {
            return $query->where('package_status', OrderStatus::PENDING);
        },
        'orders.orderPackages.packageItems' => function ($query) {
            return $query->where('item_status', OrderStatus::PENDING);
        }]);

        return $combined_order && $this->orderService->confirmCombinedOrder($combined_order)
        ? redirect()->back()->with(['success' => 'Order has been Confirmed successfully'])
        : redirect()->back()->with(['error' => 'Order Confimation Failed']);
    }

    /**
    * function to confirm the seller sub order
    * @param int|string $order
    */
    public function confirmOrder(int|string|Model $order)
    {
        $order = $this->orderRepository->find($order, ['orderPackages' => function ($query) {
            return $query->where('package_status', OrderStatus::PENDING);
        },
        'orderPackages.packageItems' => function ($query) {
            return $query->where('item_status', OrderStatus::PENDING);
        }]);

        return $order && $this->orderService->confirmOrder($order)
        ? redirect()->back()->with(['success' => 'Order has been Confirmed successfully'])
        : redirect()->back()->with(['error' => 'Order Confimation Failed']);
    }

    /**
    * function to confirm order package
    * @param int|string|Model $package
    */
    public function confirmOrderPackage(int|string|Model $package)
    {
        $package = $this->orderPackageRepository->find($package, ['order.orderPackages.packageItems' => function ($query) {
            return $query->where('item_status', OrderStatus::PENDING);
        }]);

        return $package && $this->orderService->confirmOrderPackage($package)
        ? redirect()->back()->with(['success' => 'Order has been Confirmed successfully'])
        : redirect()->back()->with(['error' => 'Order Confimation Failed']);
    }
}
