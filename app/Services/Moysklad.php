<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryExternalSource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderExternalSource;
use App\Models\Product;
use App\Models\ProductExternalSource;
use App\Models\UserExternalSource;
use App\Services\Moysklad\Client;
use App\Services\Moysklad\CounterpartyService;
use App\Services\Moysklad\CurrenciesService;
use App\Services\Moysklad\ProductService;
use App\Services\Moysklad\Service;
use App\Services\Moysklad\StockService;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Moysklad\OrderService;
use App\Services\Moysklad\CategoryService;

class Moysklad
{
    public function uploadProduct(int $product_id)
    {

        $product = Product::where('id', $product_id)->whereIn('type', [1, 2])->with('variants')->whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->first();

        ProductService::upload($product);
    }

    /**
     * Update product in moy-sklad
     *
     * @param $product
     */
    public function updateProduct($product)
    {
        ProductService::updateMoyskladProducts([$product]);
    }

    /**
     * Upload all products from database to moy-sklad
     *
     * @param $sync_id
     */
    public function uploadProducts($sync_id)
    {
        $productsForUpload = Product::whereIn('type', [1, 2])->with('variants')->whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $productsForUpdate = Product::whereIn('type', [1, 2])->with('variants.externalSource', 'externalSource')->whereHas('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $count = $productsForUpload->count() + $productsForUpdate->count();

        $sync = Service::getSync($sync_id);

        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        if ($productsForUpload->count()) {
            ProductService::upload($productsForUpload, $sync);
        }
        if ($productsForUpdate->count()) {
            ProductService::updateMoyskladProducts($productsForUpdate, $sync);
        }

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Download all products from moy-sklad to database
     *
     * @param $sync_id
     */
    public function downloadProducts($sync_id)
    {
        $sync = Service::getSync($sync_id);

        $assortment = ProductService::getAssortments($sync, 'product', $sync->current);

        if (!$sync->total) {
            $sync->total = $assortment['total'];
            $sync->update();
        }

        foreach ($assortment['rows'] as $product) {
            $productExternalSource = ProductExternalSource::where('external_product_id', $product->id)
                ->where('external_type', config('syncs.moysklad.externalCode'))
                ->with('product')
                ->first();
            $dbProduct = null;

            if (is_null($productExternalSource)){
                $dbProduct = ProductService::storeDBProduct($product, $sync);
            }
            if (!is_null($productExternalSource) && !is_null($productExternalSource->product_id)) {
                $dbProduct = ProductService::updateDBProduct($productExternalSource->product, $product, $sync);
            }

            /* */

            if (!is_null($dbProduct) && $dbProduct && $dbProduct->type === 2) {

                $client = new Client();

                $res = $client->getGuzzle('variant', [
                    'query' => [
                        'filter' => 'productid=' . $product->id,
                        'limit' => 100
                    ]
                ]);

                $variants = json_decode($res->getBody()->getContents())->rows;

                foreach ($variants as $variant) {
                    $dbVariant = ProductExternalSource::where('external_product_id', $variant->id)
                        ->where('external_type', config('syncs.moysklad.externalCode'))
                        ->with('product')
                        ->first();

                    $variant->name = $product->name;

                    if (is_null($dbVariant)){
                        ProductService::storeDBVariant($variant, $dbProduct, $sync);
                    }
                    if (!is_null($dbVariant) && !is_null($dbVariant->product_id)) {
                        ProductService::updateDBVariant($dbVariant->product, $variant, $sync);
                    }
                }

                //include product
                $dbProduct->status = 1;
                $dbProduct->update();

            }

            /* */

            $sync->current++;
            $sync->success_products_count++;
            $sync->update();
        }

        if (!empty($assortment['rows'])) {
            $this->downloadProducts($sync->id);
        }
        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    public function uploadCategory(int $category_id)
    {
        $category = Category::with('parent.externalSource', 'translates', 'externalSource')->whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->where('id', $category_id)->first();

        CategoryService::recursiveCategories([$category]);

    }

    /**
     * Update category in moy-sklad
     *
     * @param $category
     */
    public function updateCategory($category)
    {
        CategoryService::updateMoyskladCategories([$category]);
    }

    /**
     * Upload all categories from database to moy-sklad
     *
     * @param $sync_id
     */
    public function uploadCategories($sync_id)
    {
        $categoriesForUpload = Category::whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $categoriesForUpdate = Category::with('externalSource')->whereHas('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $categoriesForUploadTree = $categoriesForUpload->toTree();
        $categoriesForUpdateTree = $categoriesForUpdate->toTree();

        $sync = Service::getSync($sync_id);

        $sync->total = count($categoriesForUpload) + count($categoriesForUpdate);
        $sync->update();

        if ($categoriesForUpload->count()) {
            CategoryService::recursiveCategories($categoriesForUploadTree, $sync);
        }
        if ($categoriesForUpdate->count()) {
            CategoryService::updateMoyskladCategories($categoriesForUpdateTree, $sync);
        }

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Download all categories from moy-sklad to database
     *
     * @param $sync_id
     */
    public function downloadCategories($sync_id)
    {
        $sync = Service::getSync($sync_id);
        $productFolders = CategoryService::getExternalCategories($sync, $sync->current);

        if (!$sync->total) {
            $sync->total = $productFolders['total'];
            $sync->update();
        }

        foreach ($productFolders['rows'] as $productFolder) {
            $categoryExternalSource = CategoryExternalSource::where('external_category_id', $productFolder->id)
                ->where('external_type', config('syncs.moysklad.externalCode'))
                ->with('category')
                ->first();

            if (is_null($categoryExternalSource)) {
                CategoryService::storeDB($productFolder, $sync);
            }

            if (!is_null($categoryExternalSource) && !is_null($categoryExternalSource->category_id)) {
                CategoryService::updateDB($productFolder, $categoryExternalSource->category, $sync);
            }

            $sync->current++;
            $sync->success_categories_count++;
            $sync->update();
        }

        if (!empty($productFolders['rows'])) {
            $this->downloadCategories($sync->id);
        }
        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Update products quantity and price from moy-sklad to database
     *
     * @param $sync_id
     * @param $type
     * @param $externalProductId
     */
    public function updatePriceQuantity($sync_id)
    {
        $sync = Service::getSync($sync_id);
        $stocks = StockService::getStocks($sync, $sync->current);

        if (!$sync->total) {
            $sync->total = $stocks['total'];
            $sync->update();
        }

        ProductService::updateQunntityAndPrice($stocks, $sync);

        if (!empty($stocks['rows'])) {
            $this->updatePriceQuantity($sync->id);
        }
        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Update products rate from moy-sklad to database
     *
     * @param $sync_id
     */
    public function updateCurrenciesRate($sync_id)
    {
        $currenciesService = new CurrenciesService();
        $sync = Service::getSync($sync_id);

        $count = count($currenciesService->getCurrencies());
        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        $currenciesService->updateDBCurrenciesRate($sync);

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    public function uploadCounterparty($user_id)
    {
        $user = User::where('id', $user_id)->whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->first();

        $counterpartyService = new CounterpartyService();

        $counterpartyService->create($user);

    }

    public function updateCounterparty($user)
    {
        CounterpartyService::updateMoyskladCounterparty($user);
    }

    /**
     * Upload all counterparties from database to moy-sklad
     *
     * @param $sync_id
     */
    public function uploadCounterparties($sync_id)
    {
        $usersForCreate = User::whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $usersForUpdate = User::with('externalSource')->whereHas('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $counterpartyService = new CounterpartyService();
        $count = $usersForCreate->count() + $usersForUpdate->count();

        $sync = Service::getSync($sync_id);

        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        if (!is_null($usersForCreate)) {
            foreach ($usersForCreate as $user) {
                $counterpartyService->create($user);
                $sync->current++;
                $sync->update();
            }
        }
        if (!is_null($usersForUpdate)) {
            foreach ($usersForUpdate as $user) {
                CounterpartyService::updateMoyskladCounterparty($user, $sync);
                $sync->current++;
                $sync->update();
            }
        }

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Download all counterparties from moy-sklad to database
     *
     * @param $sync_id
     * @throws \Throwable
     */
    public function downloadCounterparty($sync_id)
    {
        $sync = Service::getSync($sync_id);

        $counterparties = CounterpartyService::getCounterparties($sync, $sync->current);

        if (!$sync->total) {
            $sync->total = $counterparties['total'];
            $sync->update();
        }

        foreach ($counterparties['rows'] as $counterparty) {
            $userExternalSource = UserExternalSource::where('external_user_id', $counterparty->id)
                ->where('external_type', config('syncs.moysklad.externalCode'))
                ->with('user')
                ->first();

            if (is_null($userExternalSource)) {
                CounterpartyService::storeDBCounterparty($counterparty);
            } else {
                CounterpartyService::updateDBCounterparty($counterparty, $userExternalSource->user, $sync);
            }

            $sync->current++;
            $sync->update();
        }


        if (!empty($counterparties['rows'])) {
            $this->downloadCounterparty($sync->id);
        }
        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    /**
     * Upload all orders on moy-sklad
     *
     * @param $sync_id
     */
    public function uploadOrders($sync_id)
    {
        $orders = Order::whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        if (!is_null($orders)) {
            $count = $orders->count();

            $sync = Service::getSync($sync_id);

            if ($sync->total == 0) {
                $sync->total = $count;
                $sync->update();
            }

            foreach ($orders as $order) {
                $user = User::where('id', $order->user_id)
                    ->whereHas('externalSource', function (Builder $query) {
                        $query->where('external_type', config('syncs.moysklad.externalCode'));
                    })->with('externalSource')->first();

                if (is_null($user)) {
                    $counterpartyService = new CounterpartyService();
                    $user = User::find($order->user_id);

                    if (!is_null($user)) {
                        $userExternalSource = $counterpartyService->create($user, $sync);
                        $external_user_id = $userExternalSource->external_user_id;
                    } else {
                        $name = $order->firstname . ' ' . $order->lastname;
                        $external_user_id = $counterpartyService->createCounterparty($name, $order->email, $order->telephone);
                    }
                } else {
                    $external_user_id = $user->externalSource[0]->external_user_id;
                }

                $orderExternalSource = OrderService::createCustomerOrder($external_user_id, $order, $sync);
                if (!is_null($orderExternalSource)) {
                    OrderService::createCustomerOrderPosition($orderExternalSource->external_order_id, $order->products, $sync);
                }

                $sync->current++;
                $sync->update();

                if ($sync->breaked) {
                    break;
                }
            }

            if (!$sync->fatal_error) {
                $sync->finished = 1;
                $sync->save();
            }
        }
    }

    public function uploadOrder(int $order_id)
    {
        $order = Order::find($order_id);

        $user = User::where('id', $order->user_id)
            ->whereHas('externalSource', function (Builder $query) {
                $query->where('external_type', config('syncs.moysklad.externalCode'));
            })->with('externalSource')->first();

        if (is_null($user)) {
            $counterpartyService = new CounterpartyService();
            $user = User::find($order->user_id);

            if (!is_null($user)) {
                $userExternalSource = $counterpartyService->create($user);
                $external_user_id = $userExternalSource->external_user_id;
            } else {
                $name = trim($order->firstname . ' ' . ($order->lastname ?: $order->surname));
                $external_user_id = $counterpartyService->createCounterparty($name, $order->email, $order->telephone);
            }
        } else {
            $external_user_id = $user->externalSource[0]->external_user_id;
        }

        $orderExternalSource = OrderService::createCustomerOrder($external_user_id, $order);

        if (!is_null($orderExternalSource)) {
            OrderService::createCustomerOrderPosition($orderExternalSource->external_order_id, $order->products);
        }
    }

    /**
     * Download all orders in database
     *
     * @param $sync_id
     */
    public function downloadOrders($sync_id)
    {
        $sync = Service::getSync($sync_id);
        $orders = OrderService::getOrders($sync, $sync->current);

        if (!$sync->total) {
            $sync->total = $orders['total'];
            $sync->update();
        }

        foreach ($orders['rows'] as $order) {
            $orderExternalSource = OrderExternalSource::where('external_order_id', $order->id)
                ->where('external_type', config('syncs.moysklad.externalCode'))
                ->first();

            if (is_null($orderExternalSource)) {
                $orderId = OrderService::storeDBOrder($order, $sync);

                if (!is_null($orderId)) {
                    $positions = OrderService::getOrderPositions($order->positions->meta->href, 25, $sync);
                    foreach ($positions as $position) {
                        OrderService::storeDBOrderPosition($position, $orderId, $sync);
                    }
                }
            }

            $sync->current++;
            $sync->update();
        }

        if (!empty($orders['rows'])) {
            $this->downloadOrders($sync->id);
        }
        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    public function uploadCurrencies($sync_id)
    {
        $currencies = Currency::all();
        $sync = Service::getSync($sync_id);
        $currenciesService = new CurrenciesService();

        $count = count($currencies);

        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        $currenciesService->uploadCurrencies($currencies, $sync);

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }

    public function downloadCurrencies($sync_id)
    {
        $sync = Service::getSync($sync_id);
        $currenciesService = new CurrenciesService();

        $count = count($currenciesService->getCurrencies());

        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        $currenciesService->downloadCurrencies($sync);

        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }
    }
}
