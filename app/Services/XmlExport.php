<?php

namespace App\Services;


use App\Helpers\FileManager;
use App\Helpers\Image;
use App\Helpers\SimpleXMLExtended;
use App\Models\Category;
use App\Models\ExportConfiguration;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class XmlExport
{
    private $export_configuration;
    private $export_settings;
    private $categories_extra_1 = [];
    private $export_products_list;

    private $categories;
    private $products;

    private $paths = [];

    private $xml;

    private $other_els = [
        'company_name',
        'site_url'
    ];

    private $currency_els = [
        'currency_code',
        'currency_rate'
    ];

    private $category_els = [
        'category_name',
        'category_id',
        'category_parent_id'
    ];

    private $product_els = [
        'product_id',
        'product_quantity_status',
        'product_url',
        'product_vendor',
        'product_quantity',
        'product_actual_price',
        'product_old_price',
        'product_currency',
        'product_sku',
        'product_description',
        'product_category_id',
        'product_images',
        'product_name',
        'product_attribute'
    ];

    public function __construct(ExportConfiguration $exportConfiguration, $force = false)
    {
        $this->export_configuration = $exportConfiguration;

        if ($force) $this->generate()->saveXml();

        try {
            $invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
            $html = \Storage::disk(config('lfm.disk'))->get($this->get_file_path());
            $xml_html = preg_replace($invalid_characters, '', $html);
            $this->xml = simplexml_load_string($xml_html);
        } catch (FileNotFoundException $exception) {
            $this->generate()->saveXml();
        }

    }

    private function prepareForGenerate()
    {
        $this->export_settings = $this->export_configuration->settings;

        $this->categories_extra_1 = Category::select('id', 'extra_1')->pluck('extra_1', 'id')->toArray();

        $this->export_products_list = $this->export_configuration->products_list;

        $this->categories = collect();

        $this->products = collect();

        if ($this->export_products_list) {

            $this->prepareSettingsByPaths();

            $this->prepareProducts();

            $this->prepareCategories();

        }
    }

    public function generate()
    {

        $this->prepareForGenerate();

        $settings_paths = $this->export_settings->paths;

        $xml = new SimpleXMLExtended("<yml_catalog date='" . date('Y-m-d H:i') . "'></yml_catalog>");

        foreach ($this->other_els as $path_code) {

            if ($this->paths[$path_code]->status) {

                switch ($path_code) {
                    case 'company_name':
                        $value = Setting::get('company_name');
                        break;
                    case 'site_url':
                        $value = config('app.url');
                        break;
                }

                if ($this->paths[$path_code]->attr) {
                    $this->addXmlChildren($xml, $this->paths[$path_code]->values)->addAttribute($this->paths[$path_code]->attr, $value);
                } else {
                    $this->addXmlChildren($xml, $this->paths[$path_code]->values){0} = $value;
                }

            }

        }

        if ($settings_paths->currency_tag->status) {

            $xml_currencies_container = $this->addXmlChildren($xml, $settings_paths->currencies_container->value);

            foreach (\App\Models\Currency::all() as $currency) {

                $xml_currency = $this->addXmlChildren($xml_currencies_container, $settings_paths->currency_tag->value);

                foreach ($this->currency_els as $path_code) {

                    if ($this->paths[$path_code]->status) {

                        switch ($path_code) {
                            case 'currency_code':
                                $value = $currency->code;
                                break;
                            case 'currency_rate':
                                $value = $currency->exchange_rate;
                                break;
                        }

                        if ($this->paths[$path_code]->attr) {
                            $this->addXmlChildren($xml_currency, $this->paths[$path_code]->values)->addAttribute($this->paths[$path_code]->attr, $value);
                        } else {
                            $this->addXmlChildren($xml_currency, $this->paths[$path_code]->values){0} = $value;
                        }

                    }

                }

            }
        }

        if ($settings_paths->category_tag->status) {

            $xml_categories_container = $this->addXmlChildren($xml, $settings_paths->categories_container->value);

            foreach ($this->categories as $category) {

                $xml_category = $this->addXmlChildren($xml_categories_container, $settings_paths->category_tag->value);

                foreach ($this->category_els as $path_code) {

                    if ($this->paths[$path_code]->status) {

                        switch ($path_code) {
                            case 'category_name':
                                $value = $category['name'];
                                break;
                            case 'category_id':
                                $value = $category['id'];
                                break;
                            case 'category_parent_id':

                                if(!$this->export_settings->has_include_parent_categories){
                                    break 2;
                                }

                                $value = $category['parent_id'];
                                break;
                        }

                        if ($this->paths[$path_code]->attr) {
                            $this->addXmlChildren($xml_category, $this->paths[$path_code]->values)->addAttribute($this->paths[$path_code]->attr, $value);
                        } else {
                            $this->addXmlChildren($xml_category, $this->paths[$path_code]->values){0} = $value;
                        }

                    }

                }

            }
        }

        if ($settings_paths->product_tag->status) {

            $xml_products_container = $this->addXmlChildren($xml, $settings_paths->products_container->value);

            foreach ($this->products as $product) {

                if(!$product->status) continue;

                $xml_product = $this->addXmlChildren($xml_products_container, $settings_paths->product_tag->value);

                $xml_description = null;

                foreach ($this->product_els as $path_code) {

                    if ($this->paths[$path_code]->status) {

                        switch ($path_code) {
                            case 'product_id':
                                $value = $product->extra_1;
                                break;
                            case 'product_quantity_status':
                                if($product->quantity < 1 || !$product->status || $product->date_available > now()){
                                    $value = 'false';
                                }else{
                                    $value = 'true';
                                }
                                break;
                            case 'product_url':
                                $value = \URL::to("$product->slug/p$product->id");
                                break;
                            case 'product_vendor':
                                $value = $product->vendor;
                                break;
                            case 'product_quantity':
                                if(!$product->status || $product->date_available > now()){
                                    $value = 0;
                                }else{
                                    $value = $product->quantity;
                                }
                                break;
                            case 'product_actual_price':
                                $value = $product->special->price ?? $product->vendor_price;
                                break;
                            case 'product_old_price':
                                $value = $product->vendor_price;
                                break;
                            case 'product_currency':
                                $value = $product->currency_code;
                                break;
                            case 'product_sku':
                                $value = $product->getOriginal('sku') ? $product->sku : '';
                                break;
                            case 'product_description':
                                $value = $this->paths[$path_code]->attr ? "<![CDATA[{$product->translate->description}]]>" : $product->translate->description;
                                break;
                            case 'product_category_id':
                                $value = $product->category_id ? $this->categories_extra_1[$product->category_id] : null;
                                break;
                            case 'product_name':
                                $value = $product->translate->name;
                                break;
                            default:
                                break;
                        }

                        if ($path_code === 'product_old_price' && $product->vendor_price >= ($product->special ? $product->special->price : 0)) continue;

                        if ($path_code === 'product_images') {

                            $id = $product->type == 3 ? $product->main_id : $product->id;

                            if ($product->image) $this->addXmlChildren($xml_product, $this->paths[$path_code]->values){0} = Image::compileImageSrc("products/{$id}/$product->image");

                            foreach ($product->images as $image) {
                                if ($image->src) $this->addXmlChildren($xml_product, $this->paths[$path_code]->values){0} = Image::compileImageSrc("products/{$id}/$image->src");
                            }
                        } elseif ($path_code === 'product_attribute') {

                            $to_attributes = $product->type == 1 ? $product->to_attributes : $product->main_product->to_attributes;

                            foreach ($to_attributes as $to_attribute) {

                                if($to_attribute->has_variant && $product->type === 3){

                                    $variant_attribute_id = $product->to_variant_attribute_values->first(function($to_variant_attr) use ($to_attribute){
                                        return $to_variant_attr->attribute_id == $to_attribute->attribute_id;
                                    })->attribute_value_id;

                                    $values = $to_attribute->values->filter(function($attr_value) use ($variant_attribute_id){
                                        return $attr_value->id == $variant_attribute_id;
                                    })->map(function ($value) {
                                        return $value->translate->value;
                                    })->toArray();

                                }else{
                                    $values = $to_attribute->values->map(function ($value) {
                                        return $value->translate->value;
                                    })->toArray();
                                }



                                if ($this->export_settings->attributes->is_selected_main && !in_array($to_attribute->attribute->id, (array)$this->export_settings->attributes->list)) {
                                    if (!$settings_paths->product_description->is_attribute) {

                                        $xml_description_attribute = $this->addXmlChildren($xml_description, $settings_paths->product_attribute->value);

                                        if ($settings_paths->product_attribute_value->is_attribute) {
                                            $this->addXmlChildren($xml_description_attribute, $settings_paths->product_attribute_value->value)->addAttribute($this->paths['product_attribute_value']->attr, $values);
                                        } else {
                                            if (count($values) > 1) {
                                                $this->addXmlChildren($xml_description_attribute, $settings_paths->product_attribute_value->value)->addCData(implode(', ', $values));
                                            } else {
                                                $this->addXmlChildren($xml_description_attribute, $settings_paths->product_attribute_value->value){0} = implode(', ', $values);
                                            }

                                        }

                                        if ($settings_paths->product_attribute_name->is_attribute) {
                                            $this->addXmlChildren($xml_description_attribute, $settings_paths->product_attribute_name->value)->addAttribute($this->paths['product_attribute_name']->attr, $to_attribute->attribute->translate->name);
                                        } else {
                                            $this->addXmlChildren($xml_description_attribute, $settings_paths->product_attribute_name->value){0} = $to_attribute->attribute->translate->name;
                                        }

                                    }
                                } else {
                                    $xml_attribute = $this->addXmlChildren($xml_product, $settings_paths->product_attribute->value);

                                    if ($settings_paths->product_attribute_value->is_attribute) {
                                        $this->addXmlChildren($xml_attribute, $settings_paths->product_attribute_value->value)->addAttribute($this->paths['product_attribute_value']->attr, $values);
                                    } else {
                                        if (count($values) > 1) {
                                            $this->addXmlChildren($xml_attribute, $settings_paths->product_attribute_value->value)->addCData(implode(', ', $values));
                                        } else {
                                            $this->addXmlChildren($xml_attribute, $settings_paths->product_attribute_value->value){0} = implode(', ', $values);
                                        }
                                    }

                                    if ($settings_paths->product_attribute_name->is_attribute) {
                                        $this->addXmlChildren($xml_attribute, $settings_paths->product_attribute_name->value)->addAttribute($this->paths['product_attribute_name']->attr, $to_attribute->attribute->translate->name);
                                    } else {
                                        $this->addXmlChildren($xml_attribute, $settings_paths->product_attribute_name->value){0} = $to_attribute->attribute->translate->name;
                                    }
                                }

                            }

                        } else {
                            if ($this->paths[$path_code]->attr) {
                                $this->addXmlChildren($xml_product, $this->paths[$path_code]->values)->addAttribute($this->paths[$path_code]->attr, $value);
                            } else {
                                if ($path_code === 'product_description') {

                                    $xml_description = $this->addXmlChildren($xml_product, $settings_paths->product_description->value);

                                    $xml_description->addCData($product->translate->description);

                                } else {
                                    $this->addXmlChildren($xml_product, $this->paths[$path_code]->values){0} = $value;
                                }
                            }
                        }

                    }

                }

            }

        }

        $this->xml = $xml;

        return $this;

    }

    public function getXmlString()
    {
        return $this->xml->saveXML();
    }

    public function saveXml()
    {
        \Storage::disk(config('lfm.disk'))->put($this->get_file_path(), $this->xml->saveXML());
    }

    private function prepareSettingsByPaths()
    {
        $settings_paths = $this->export_settings->paths;

        if ($settings_paths) {

            foreach ($settings_paths as $path_code => $path) {

                if ($settings_paths->{$path_code}->status && $settings_paths->{$path_code}->is_attribute) {
                    $attr = array_pop($settings_paths->{$path_code}->value);
                } else {
                    $attr = null;
                }

                $this->paths[$path_code] = (object)[
                    'attr' => $attr,
                    'values' => $settings_paths->{$path_code}->value,
                    'status' => $settings_paths->{$path_code}->status
                ];

            }
        }
    }

    private function prepareProducts()
    {
        $this->products = $this->export_products_list->products;

        $this->products->load(
            'translates',
            'to_attributes.attribute.translates',
            'to_attributes.values.translates',
            'main_product.to_attributes.attribute.translates',
            'main_product.to_attributes.values.translates',
            'to_variant_attribute_values',
            'images',
            'specials'
        );

    }

    private function prepareCategories()
    {
        $categories_ids = Product::whereIn('id', $this->products->pluck('id'))->where('category_id', '<>', null)->select('category_id')->pluck('category_id');

        if ($this->export_settings->has_include_parent_categories) {
            Category::with('ancestors')->find($categories_ids->unique())->each(function ($category) {

                foreach ($category->ancestors as $ancestor) {
                    $this->addCategoryToListIfNotPresent($ancestor);
                }

                $this->addCategoryToListIfNotPresent($category);

            });
        } else {
            Category::find($categories_ids->unique())->each(function ($category) {
                $this->addCategoryToListIfNotPresent($category);
            });
        }
    }

    private function addCategoryToListIfNotPresent(Category $category)
    {
        if (!$this->categories->firstWhere('id', $category->extra_1)) {

            $this->categories->push([
                'id' => $category->extra_1,
                'parent_id' => $category->parent_id ? $this->categories_extra_1[$category->parent_id] : 0,
                'name' => $category->translate->marketplace_name
            ]);

        }
    }

    private function addXmlChildren(SimpleXMLExtended $xml, $values)
    {
        $max_length = count($values);

        foreach ($values as $i => $value) {
            if (!$xml->xpath($value) || $i + 1 == $max_length) {
                $xml = $xml->addChild($value);
            } else {
                $xml = $xml->{$value};
            }
        }

        return $xml;
    }

    private function get_file_path()
    {
        return 'uploads/files/export/export-configuration-' . $this->export_configuration->id . '.xml';
    }

}