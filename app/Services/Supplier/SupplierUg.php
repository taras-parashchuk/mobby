<?php

namespace App\Services\Supplier;

use App\Helpers\HelperFunction;
use App\Models\Attribute;
use App\Models\SupplierCategory;
use App\Models\SupplierProduct;
use App\Models\UgContractMediaCache;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\ServiceFactory;
use Illuminate\Support\Facades\DB;

class SupplierUg extends SupplierBase
{

    public function __construct()
    {
        parent::__construct('UgContract');

    }

    public function sync()
    {
        return $this;
    }

    public function downloadAndSaveProducts()
    {

    }

    private function getProducts($page)
    {
	return $this;
    }

    public function downloadProduct( SupplierProduct $supplier_product )
    {
	$images = [];
        $attributes = [];

	$uuid = $supplier_product->supplier_uuid;
	
	$desc = DB::table('UgContractMediaCache')->where('supplier_uuid', $uuid)->first();
	
	try {
	    $dd = $desc->description_ua;
	} catch (\Exception $exception) {
	    
	    \Log::error('Can\'t get detail info from MediaCache UgContact ' . $exception->getMessage());
	
	    return [
            'attributes' => $attributes,
            'images' => $images,
            'description' => '',
            'vendor' => ''
    	    ];
	}
	
	
	$attrs = DB::table('UgContractAttrs')->where('supplier_uuid', $uuid)->get();
	$imgs = DB::table('UgContractImages')->where('supplier_uuid', $uuid)->get();

	foreach ($imgs as $img) {
	    $images[] = (string)$img->image_link;
	}

	foreach ($attrs as $attr) {

                $attribute = HelperFunction::getAttributeOrCreate($attr->attribute_name);
                $attribute_value = HelperFunction::getAttributeValueOrCreate($attribute, (string)$attr->attribute_val);

		if(isset($attributes[$attribute->id])){
		    $attributes[$attribute->id]['values'][] = $attribute_value;
		}else{
		    $attributes[$attribute->id] = [
		    'attribute' => $attribute,
		    'values' => [$attribute_value]];
		}
        }
	
        return [
            'attributes' => $attributes,
            'images' => $images,
            'description' => $desc->description_ua,
            'vendor' => $desc->vendor
        ];
    }

}
