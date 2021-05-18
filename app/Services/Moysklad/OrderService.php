<?php

namespace App\Services\Moysklad;

use App\Models\AttributeToProduct;
use App\Models\AttributeTranslation;
use App\Models\Order;
use App\Models\OrderExternalSource;
use App\Models\OrderProduct;
use App\Models\OrderProductExternalSource;
use App\Models\Product;
use App\Models\ProductExternalSource;
use App\Models\Setting;
use App\Models\UserExternalSource;

class OrderService
{
    /**
     * Create new order on moy-sklad
     *
     * @param $external_user_id
     * @param $order
     * @return OrderExternalSource
     */
    public static function createCustomerOrder($external_user_id, $order, $sync = null)
    {
        $organizationService = new OrganizationService();
        $client = new Client();
        $currenciesService = new CurrenciesService();

        $organization = $organizationService->getOrganization();
        $counterpartyMeta = Service::getMeta($external_user_id, 'counterparty');

        $data = [
            'organization' => Service::formMeta($organization->meta),
            'agent' => Service::formMeta($counterpartyMeta),
            'applicable' => false
        ];

        $data['rate'] = (object)[
            'currency' => Service::formMeta($currenciesService->getCurrency($order->currency_code)->meta)
        ];

        if (!is_null($order->comment)) {
            $data['description'] = $order->comment;
        }

        // Check if user exist in moy-sklad
        $userPath = 'counterparty/' . $external_user_id;
        try {
            $counterparty = $client->getGuzzle($userPath);
        } catch (\Exception $exception) {
            Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.external_counterparty_not_exist', [
                    'id' => $order->id,
                ]
            ), $sync);
        }

        if (isset($counterparty)) {
            try {
                $res = $client->postGuzzle('customerorder', [
                    'json' => $data
                ]);
            } catch (\Exception $exception) {
                Service::exception($exception, $sync);
            }

            $orderExternalSource = new OrderExternalSource([
                'external_order_id' => json_decode($res->getBody()->getContents())->id,
                'external_type' => config('syncs.moysklad.externalCode')
            ]);

            $orderExternalSource = $order->externalSource()->save($orderExternalSource);

            return $orderExternalSource;
        }

        return null;
    }

    /**
     * Create new order position on moy-sklad
     *
     * @param $external_order_id
     * @param $orderProducts
     */
    public static function createCustomerOrderPosition($external_order_id, $orderProducts, $sync = null)
    {
        $client = new Client();

        foreach ($orderProducts as $orderProduct) {
            if (!is_null($orderProduct->product_id)) {
                $data = [];
                $type = 'product';

                foreach ($orderProduct->product->externalSource as $item) {
                    if ($item->external_type == config('syncs.moysklad.externalCode')) {
                        $productMeta = Service::getMeta($item->external_product_id, $type);
                        $data['assortment'] = Service::formMeta($productMeta);
                    }
                }
                $data['price'] = $orderProduct->price * 100;
                $data['quantity'] = $orderProduct->quantity;

                $path = 'customerorder/' . $external_order_id . '/positions';

                if (array_key_exists('assortment', $data)) {
                    try {
                        $res = $client->postGuzzle($path, [
                            'json' => $data
                        ]);
                    } catch (\Exception $exception) {
                        Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.error', [
                                'product' => $orderProduct->product_id,
                                'order' => $orderProduct->order_id
                            ]
                        ), $sync);
                        Service::exception($exception);
                    }

                    if (isset($res)) {
                        $orderProductExternalSource = new OrderProductExternalSource([
                            'external_order_product_id' => json_decode($res->getBody()->getContents())[0]->id,
                            'external_type' => config('syncs.moysklad.externalCode')
                        ]);

                        $orderProduct->externalSource()->save($orderProductExternalSource);
                    }
                } else {
                    if ($orderProduct->product->type == 3) {
                        $productExternalSource = ProductExternalSource::where('product_id', $orderProduct->product->main_id)
                            ->where('external_type', config('syncs.moysklad.externalCode'))
                            ->with('product')
                            ->first();

                        if (is_null($productExternalSource)) {
                            $product = Product::find($orderProduct->product->main_id);
                            ProductService::upload($product);

                        } else {
                            ProductService::uploadVariants([$orderProduct->product]);
                        }
                    } else {
                        ProductService::upload($orderProduct->product);
                    }
                    $orderProduct->refresh();

                    foreach ($orderProduct->product->externalSource as $item) {
                        if ($item->external_type == config('syncs.moysklad.externalCode')) {
                            $productMeta = Service::getMeta($item->external_product_id, $type);
                            $data['assortment'] = Service::formMeta($productMeta);
                        }
                    }
                    try {
                        $res = $client->postGuzzle($path, [
                            'json' => $data
                        ]);
                    } catch (\Exception $exception) {
                        Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.error', [
                                'product' => $orderProduct->product_id,
                                'order' => $orderProduct->order_id
                            ]
                        ), $sync);
                        Service::exception($exception);
                    }
                }
            } else {
                Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.product_not_exist', [
                        'id' => $orderProduct->order_id
                    ]
                ), $sync);
            }
        }
    }

    /**
     * Update order position on moy-sklad
     *
     * @param $order
     */
    public static function updateCustomerOrderPosition($order)
    {
        $client = new Client();
        $orderProducts = $order->products;
        $external_order_id = null;

        foreach ($order->externalSource as $item) {
            if ($item->external_type == config('syncs.moysklad.externalCode')) {
                $external_order_id = $item->external_order_id;
            }
        }

        foreach ($orderProducts as $orderProduct) {
            $data = [];
            $type = $orderProduct->product->type == 3 ? 'variant' : 'product';
            foreach ($orderProduct->product->externalSource as $item) {
                if ($item->external_type == config('syncs.moysklad.externalCode')) {
                    $productMeta = Service::getMeta($item->external_product_id, $type);
                    $data['assortment'] = Service::formMeta($productMeta);
                }
            }
            $data['price'] = $orderProduct->price * 100;
            $data['quantity'] = $orderProduct->quantity;
            $externalSource = $orderProduct->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
            $externalOrderProductId = $externalSource->external_order_product_id;

            $path = 'customerorder/' . $external_order_id . '/positions' . $externalOrderProductId;

            try {
                $client->putGuzzle($path, [
                    'json' => $data
                ]);
            } catch (\Exception $exception) {
                Service::exception($exception);
            }
        }
    }

    /**
     * Get all orders from moy-sklad
     *
     * @param $sync
     * @param int $limit
     * @return array
     */
    public static function getOrders($sync, $offset = 0, $limit = 25)
    {
        $orders = [];
        $client = new Client();

        try {
            $res = $client->getGuzzle('customerorder', [
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit,
                ]
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
            return $orders;
        }

        $orders_info = json_decode($res->getBody()->getContents());

        $orders['rows'] = $orders_info->rows;
        $orders['total'] = $orders_info->meta->size;

        return $orders;
    }

    /**
     * Get all order positions from moy-sklad
     *
     * @param $sync
     * @param $href
     * @param int $limit
     * @return array
     */
    public static function getOrderPositions($href, $limit = 25, $sync = null)
    {
        $offset = 0;
        $positions = [];
        $client =new Client();

        while (true) {
            try {
                $res = $client->getGuzzle( $href, [
                    'query' => [
                        'offset' => $offset,
                        'limit' => $limit,
                    ]
                ]);
            } catch (\Exception $exception) {
                Service::exception($exception, $sync);
            }
            $data = json_decode($res->getBody()->getContents())->rows;
            $positions = array_merge($positions, $data);

            if (count($data) < $limit) {
                break;
            }
            $offset += $limit;
        }

        if (!is_null($sync) && empty($positions)){
            Service::setFatalError($sync);
            Service::break($sync->job_id);
        }

        return $positions;
    }

    /**
     * Create new order in database
     *
     * @param $order
     * @return mixed
     * @throws \Throwable
     */
    public static function storeDBOrder($order, $sync)
    {
        $dbOrder = new Order();

        // Extraction database user
        $externalUserId = Service::extractIdFromMeta($order->agent->meta->uuidHref);
        $userExternalSource = UserExternalSource::where('external_user_id', $externalUserId)
            ->where('external_type', config('syncs.moysklad.externalCode'))
            ->with('user')
            ->first();

        //Extraction currency iso code
        $currencyService = new CurrenciesService();
        $isoCode = $currencyService->getExternalIsoCode($order->rate->currency->meta->href);
        $dbOrder->currency_code = $isoCode;
        $dbOrder->locale = Setting::get('admin_language');

        $totals[] = [
            'code' => 'total',
            'name' => trans('cart.text.total'),
            'value' => $order->sum / 100,
            'sort_order' => 0
        ];

        $dbOrder->comment = property_exists($order, 'description') ? $order->description : null;

        if (!is_null($userExternalSource)) {
            $dbUser = $userExternalSource->user;

            $dbOrder->email = $dbUser->email;
            $dbOrder->telephone = $dbUser->telephone ?? '';
            $dbOrder->user_id = $dbUser->id;
            $dbOrder->user_group_id = $dbUser->group_id;
            $dbOrder->firstname = $dbUser->firstname;
            $dbOrder->lastname = $dbUser->lastname;
        } else {
            $dbOrder->telephone = '';
        }

        $orderExternalSource = new OrderExternalSource([
            'external_order_id' => $order->id,
            'external_type' => config('syncs.moysklad.externalCode')
        ]);

        \DB::transaction(function () use ($dbOrder, $totals, $orderExternalSource) {

            $dbOrder->save();

            $dbOrder->totals()->createMany($totals);

            $dbOrder->externalSource()->save($orderExternalSource);

        });

        if (is_null($userExternalSource)) {
            Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.user_not_related', [
                'id' => $dbOrder->id
            ]), $sync);
        }

        return $dbOrder->id;
    }

    /**
     * Create new order product in database
     *
     * @param $position
     * @param $orderId
     * @throws \Throwable
     */
    public static function storeDBOrderPosition($position, $orderId, $sync = null)
    {
        // Extraction database user
        $externalProductId = Service::extractId($position->assortment->meta->href);
        $productExternalSource = ProductExternalSource::where('external_product_id', $externalProductId)
            ->where('external_type', config('syncs.moysklad.externalCode'))
            ->with('product')
            ->first();

        if (is_null($productExternalSource)) {
            Service::writeLog(config('syncs.moysklad.dataTypes.order'), trans('moy-sklad.errors.order.external_product_not_exist', [
                'product_url' => $position->assortment->meta->uuidHref,
                'order_id' => $orderId,
            ]), $sync);
        } else {
            $dbOrderProduct = new OrderProduct();
            $product = $productExternalSource->product;

            $dbOrderProduct->order_id = $orderId;
            $dbOrderProduct->product_id = $product->id;
            $dbOrderProduct->sku = $product->sku;
            $dbOrderProduct->name = $product->translate->name;
            $dbOrderProduct->quantity = $position->quantity;
            $dbOrderProduct->price = $position->price / 100;

            $attributeToProducts = AttributeToProduct::where('product_id', '923371356')->get();

            if (!is_null($attributeToProducts)) {
                $specification = [];
                foreach ($attributeToProducts as $item) {
                    $attributeTranslate = AttributeTranslation::where('attribute_id', $item->attribute->id)
                        ->where('locale', Setting::get('admin_language'))
                        ->first();

                    foreach ($item->to_values as $to_value) {
                        foreach ($to_value->attribute_value->translates as $translate)
                            if ($translate->locale == Setting::get('admin_language')) {
                                $specification[$attributeTranslate->name] = $translate->value;
                            }
                    }
                }
                $dbOrderProduct->specification = json_encode($specification, JSON_UNESCAPED_UNICODE);
            }

            $orderProductsExternalSource = new OrderProductExternalSource([
                'external_order_product_id' => $position->id,
                'external_type' => config('syncs.moysklad.externalCode')
            ]);

            \DB::transaction(function() use ($dbOrderProduct, $orderProductsExternalSource){

                $dbOrderProduct->save();

                $dbOrderProduct->externalSource()->save($orderProductsExternalSource);

            });
        }
    }
}
