<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\AttributeToProduct;
use App\Models\AttributeToSupplierCategory;
use App\Models\AttributeValueToProduct;
use App\Models\Category;
use App\Models\ImageToProduct;
use App\Models\Product;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Models\SupplierCategory;
use App\Models\SupplierProduct;
use App\Services\Supplier\SupplierElko;
use App\Services\Moysklad\ProductService;
use App\Services\Moysklad\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;


class SupplierTest extends Controller
{
    //
    public function index(Request $request)
    {
        
        ini_set('max_execution_time', -1);
        error_reporting(E_ALL);

        $ps = new ProductService;

        $type = config('syncs.moysklad.dataTypes.product');
        //$sync = Service::createSync($type);

        //$sync->save();

        $productsForUpload = Product::whereIn('type', [1, 2])->with('variants')->whereDoesntHave('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $productsForUpdate = Product::whereIn('type', [1, 2])->with('variants.externalSource', 'externalSource')->whereHas('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        $count = $productsForUpload->count() + $productsForUpdate->count();

        $sync = Service::getSync(610);

        if ($sync->total == 0) {
            $sync->total = $count;
            $sync->update();
        }

        $PS = 1000;

        $steps = (int)($productsForUpload->count() / $PS);
        $rem = (int)($productsForUpload->count() - $steps * $PS);

        if ($productsForUpload->count() > $PS) {
            for ($step = 0; $step < $steps; $step++) {
                $sliced_products_upload = $productsForUpload->slice($step * $PS, $PS);
                ProductService::upload($sliced_products_upload, $sync);
            }
            ProductService::upload($productsForUpload->slice($steps * $PS, $rem), $sync);
        } else {
            ProductService::upload($productsForUpload->slice($steps * $PS, $rem), $sync);
        }

        $steps = (int)($productsForUpdate->count() / $PS);
        $rem = (int)($productsForUpdate->count() - $steps * $PS);

        if ($productsForUpdate->count() > $PS) {
            for ($step = 0; $step < $steps; $step++) {
                $sliced_products_upload = $productsForUpdate->slice($step * $PS, $PS);
                ProductService::updateMoyskladProducts($sliced_products_upload, $sync);
            }
            ProductService::updateMoyskladProducts($productsForUpdate->slice($steps * $PS, $rem), $sync);
        } else {
            ProductService::updateMoyskladProducts($productsForUpdate->slice($steps * $PS, $rem), $sync);
        }


        if (!$sync->fatal_error) {
            $sync->finished = 1;
            $sync->save();
        }

        // ini_set('display_errors', '1');
        
        // $supp = new SupplierElko;

        // //$supp->downloadAndSaveProducts();

        // $supp->sync();


        // $supp->downloadProducts('ICU');
        // if ($supp->isProducts()) {
        //     $supp->saveProducts();
        // }
    

    }

    public function update(Request $request, $supplier_product_id)
    {
        
    }

    private function getProductsSku(): array
    {
        return Product::without('translates')->select('sku')->pluck('sku')->unique()->toArray();
    }

}



?>