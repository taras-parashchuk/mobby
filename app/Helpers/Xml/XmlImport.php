<?php

namespace App\Helpers\Xml;

use App\Helpers\FileManager;
use App\Models\Attribute;
use App\Models\AttributeToProduct;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueToProduct;
use App\Models\AttributeValueTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Currency;
use App\Models\ImageToProduct;
use App\Models\Product;
use App\Models\ProductSpecial;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Models\Sync;
use App\Models\SyncConfiguration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use DB;

class XmlImport
{
    private $sync_id;
    private $sync;
    private $content;
    private $locale;
    private $configuration;
    private $currencies;
    private $default_currency;
    private $default_user_group;


    private $categories;
    private $products;
    private $extra_2 = '';//source domain
    private $categories_count;
    private $products_count;
    private $relation_categories;

    private $configuration_paths;
    private $configuration_creating_updating;
    private $missingProductsAction;

    public function __construct($sync_id, string $file_path)
    {
        $this->sync_id = $sync_id;

        try {
            $this->sync = Sync::findOrFail($sync_id);
        } catch (ModelNotFoundException $e) {
            $this->setFatalError("Sync with id $sync_id not found", true);
        }

        if(!$this->sync->manually){

            if($this->sync->finished) $this->clearSync($this->sync);

            if(!\Storage::disk('uploads')->has($file_path)){
                \Storage::disk('uploads')->put($file_path, file_get_contents($this->sync->link));
            }
        }

        try {
            $this->content = new SimpleXMLElement(\Storage::disk('uploads')->get($file_path));
        } catch (\Throwable $exception) {
            $this->setFatalError(trans('validation.custom.file-can-not-read'));
        }

        $this->locale = Setting::get('site_language');

        try {
            $this->configuration = SyncConfiguration::find($this->sync->configuration_id)->settings;
        } catch (\ErrorException $e) {
            $this->setFatalError(trans('validation.custom.configuration-not-exists'));
        }

        try {
            $this->configuration_creating_updating = $this->configuration->creating_updating;
        } catch (\ErrorException $e) {
            $this->setFatalError(trans('validation.custom.configuration-is-broken'));
        }

        try {
            $this->missingProductsAction = $this->configuration->missingProductsAction;
        } catch (\ErrorException $e) {
            $this->setFatalError(trans('validation.custom.configuration-is-broken'));
        }

        try {
            $this->configuration_paths = $this->configuration->paths;
        } catch (\ErrorException $e) {
            $this->setFatalError(trans('validation.custom.configuration-is-broken'));
        }

        if($this->isActualPathSettings('category_name', null) || $this->isActualPathSettings('category_parent_id', null)){
            $categories_container = $this->getElementByPath($this->content, 'categories_container');

            if($categories_container !== null && $categories_container->children()){
                $this->categories = $this->getElementByPath($categories_container, 'category_tag', [
                    'notification_type' => 2
                ]);
            }else{
                $this->categories = [];
            }
        }else{
            $this->categories = [];
        }

        $this->categories_count = count($this->categories);

        $products_container = $this->getElementByPath($this->content, 'products_container');

        if($products_container !== null && $products_container->children()){
            $this->products = $this->getElementByPath($products_container, 'product_tag', [
                'notification_type' => 2
            ]);
        }else{
            $this->products = [];
        }

        $this->products_count = count($this->products);

        $this->extra_2 = $this->getElementByPath( $this->content, 'site_source', [
            'notification_type' => 0
        ]);

        if( !$this->extra_2 || ( $this->extra_2->attributes() || $this->extra_2->children()->count() )) {
            $this->extra_2 = null;
        }else{
            $this->extra_2 = (string)$this->extra_2;
        }

        $this->default_currency = Setting::get('currency');

        $this->currencies = Currency::where('active', 1)->pluck('code')->toArray();

        $this->default_user_group = Setting::get('user_group_before_register');

        $this->relation_categories = $this->configuration->relationCategories->categories ?? [];

        if($this->relation_categories){

            $tmp = [];

            foreach ($this->relation_categories as $rel_category){
                $tmp[$rel_category->id] = $rel_category->site_category_id;
            }

            $this->relation_categories = $tmp;

            unset($tmp);
        }

    }

    public function execute()
    {
        switch ($this->sync->type) {
            case 'Products':
                $this->products($this->sync->current);
                break;
            case 'Categories':
            default:
                if($this->missingProductsAction === 'remove') $this->deleteOldProducts();
                $this->categories($this->sync->current);
                break;

        }
    }

    private function categories(int $current)
    {

        $this->sync->type = 'Categories';
        $this->sync->current = $current;
        $this->sync->total = $this->categories_count;

        $this->sync->update();


        for($i = 0; $i < $this->categories_count; $i++){

            if ($current > $i) continue;

            $category = $this->categories[$i];

            $extra_1 = (string)$this->getElementByPath($category, 'category_id', [
                'notification_type' => 1,
                'text' => trans('validation.custom.sync.category_id_not_found', ['number' => $i + 1])
            ]);

            if(!$extra_1) continue;

            $id = $this->getCategoryId($extra_1);

            if(isset($this->relation_categories[$extra_1]) && $id === $this->relation_categories[$extra_1] && Category::where('id', $this->relation_categories[$extra_1])->value('id')){

                $this->sync->current++;
                $this->sync->success_categories_count++;

                $this->sync->update();

                continue;
            }

            //false якщо оновлювати або створювати дані не потрібно, null якщо значення пусте
            if($this->isActualPathSettings('category_parent_id', $id ? 1 : 0)){
                $parent_extra_1 = (string)$this->getElementByPath($category, 'category_parent_id', [
                    'notification_type' => 0,
                    'text' => trans('validation.custom.sync.parent_category_id_not_found', ['number' => $i + 1])
                ]);
            }else{
                $parent_extra_1 = false;
            }

            if($parent_extra_1 !== false){
                $parent_id = $this->getCategoryId($parent_extra_1);
            }else{
                $parent_id = false;
            }

            //якщо створюємо або оновлюємо, то назва категорії обовязкова, за відсутності обовязкового параметру пропускаємо
            if(!$id || $this->isActualPathSettings('category_name', 1)){

                $name = (string)$this->getElementByPath($category, 'category_name', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.sync.category_name_not_found', ['number' => $i + 1])
                ]);

                if(!$name) continue;

            }else{
                //назва не оновлюється
                $name = false;
            }

            if (!$id) {

                $eCategory = new Category();

                $eCategory->extra_1 = $extra_1;
                $eCategory->extra_2 = $this->extra_2;
                $eCategory->slug = \Str::slug($name, '-', $this->locale);
                $eCategory->status = true;

                if ($parent_id !== false) $eCategory->parent_id = $parent_id;

                $eCategoryTranslation = new CategoryTranslation();

                $eCategoryTranslation->locale = $this->locale;
                $eCategoryTranslation->name = $name;

                try{
                    DB::transaction(function() use ($eCategory, $eCategoryTranslation) {
                        $eCategory->save();
                        $eCategory->translates()->save($eCategoryTranslation);
                    });
                }catch (\Throwable $error){
                    try{
                        DB::rollBack();
                    }catch (\Exception $exception){

                    }
                }

            }else{

                $eCategory = Category::find($id);

                if ($parent_id !== false){
                    $eCategory->parent_id = $parent_id;
                    $eCategory->update();
                }

                $eCategoryTranslation = $eCategory->translate;

                if($name !== false){
                    $eCategoryTranslation->name = $name;
                    $eCategoryTranslation->update();
                }

            }

            $this->sync->current++;
            $this->sync->success_categories_count++;

            $this->sync->update();


            if ($this->sync->current === $this->sync->total) {
                break;
            }

            $this->sync->refresh();

            if ($this->sync->breaked) {
                break;
            }
        }

        if ($this->sync->breaked) $this->break();

        $this->products(0);
    }

    private function products(int $current)
    {

        $this->sync->type = 'Products';
        $this->sync->current = $current;
        $this->sync->total = $this->products_count;

        $this->sync->update();


        for($i = 0; $i < $this->products_count; $i++){
            if ($current > $i) continue;

            $product = $this->products[$i];

            $extra_1 = (string)$this->getElementByPath($product, 'product_id', [
                'notification_type' => 1,
                'text' => trans('validation.custom.sync.product_id_not_found', ['number' => $i + 1])
            ]);

            if(!$extra_1) continue;

            $id = $this->getProductId($extra_1);

            $eProduct = Product::findOrNew($id);

            if (!$eProduct->id) {

                $eProduct->status = true;
                $eProduct->extra_1 = $extra_1;
                $eProduct->type = 1;
                $eProduct->extra_2 = $this->extra_2;

                $creating = true;

            } else {
                $creating = false;
            }

            //якщо створюємо або оновлюємо, то назва товару обовязкова, за відсутності обовязкового параметру пропускаємо
            if(!$id || $this->isActualPathSettings('product_name', 1)){

                $name = (string)$this->getElementByPath($product, 'product_name', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.sync.product_name_not_found', ['number' => $i + 1])
                ]);

                if(!$name) continue;

            }else{
                //назва не оновлюється
                $name = false;
            }

            if($this->isActualPathSettings('product_quantity', $creating ? 0 : 1)){

                $available = (string)$this->getElementByPath($product, 'product_quantity', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_quantity'), 'number' => $i + 1 ])
                ]);

                if($available === 'true'){
                    $eProduct->quantity = 100;
                }elseif ($available === 'false'){
                    $eProduct->quantity  =  0;
                }else{
                    $eProduct->quantity = (int)$available;
                }

            }elseif($creating){
                $eProduct->quantity = 100;
            }


            if($this->isActualPathSettings('product_currency', $creating ? 0 : 1)){

                $currency_code = (string)$this->getElementByPath($product, 'product_currency', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_currency'), 'number' => $i + 1 ])
                ]);

                if (!in_array($currency_code, $this->currencies)){
                    $this->increaseWarning( trans('validation.custom.sync.currency_not_found', ['id' => $extra_1, 'currency_code' => (string)$product->currencyId ]) );
                    continue;
                }else{
                    $eProduct->currency_code = $currency_code;
                }

            }elseif($creating){
                $eProduct->currency_code = $this->default_currency;
            }

            if($this->isActualPathSettings('product_price', $creating ? 0 : 1)){

                $price = (string)$this->getElementByPath($product, 'product_price', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_price'), 'number' => $i + 1 ])
                ]);

                $price = preg_replace('#\s#ui', '', $price);

                $eProduct->vendor_price = $price;

            }elseif ($creating){
                $eProduct->vendor_price = 0;
            }

            if($this->isActualPathSettings('product_discount', $creating ? 0 : 1)){

                $discount = (string)$this->getElementByPath($product, 'product_discount', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_discount'), 'number' => $i + 1 ])
                ]);

                $discount = preg_replace('#\s#ui', '', $discount);

                if($discount !== ''){

                    $special = new ProductSpecial();
                    $special->price = preg_replace('#\s#ui', '', $discount);
                    $special->user_group_id = $this->default_user_group;

                }

            }else{
                $special = false;
            }

            if($creating || $this->isActualPathSettings('product_slug', 1)){
                $eProduct->slug = \Str::slug($name, '-', $this->locale);
            }

            if($this->isActualPathSettings('product_category_id', !$creating)){

                $category_id = (string)$this->getElementByPath($product, 'product_category_id', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_category_id'), 'number' => $i + 1 ])
                ]);

                $eProduct->category_id = $this->getCategoryId($category_id);

                /// 2.Categories
                $categories_list = $this->getGroupCategories($eProduct->category_id);

                $to_compare_categories = $to_categories = [];

                foreach ($categories_list as $categories_list_id) {
                    $p_t_compare_category = new ProductToCompareCategory();

                    $p_t_compare_category->category_id = $categories_list_id;

                    $p_t_category = new ProductToCategory();

                    $p_t_category->category_id = $categories_list_id;

                    $to_compare_categories[] = $p_t_compare_category;
                    $to_categories[] = $p_t_category;
                }

            }else{
                $to_compare_categories = $to_categories = false;
            }

            if($this->isActualPathSettings('product_sku', null)){

                $sku = (string)$this->getElementByPath($product, 'product_sku', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_sku'), 'number' => $i + 1 ])
                ]);

                $eProduct->sku = $sku;

            }

            /// 1.Translates

            if($this->isActualPathSettings('product_description', null)){

                $description = (string)$this->getElementByPath($product, 'product_description', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_description'), 'number' => $i + 1 ])
                ]);

            }else{
                $description = false;
            }

            if($creating){
                $eProductTranslation = new ProductTranslation();
                $eProductTranslation->locale = $this->locale;
            }else{

                $eProductTranslation = $eProduct->translate;

                if(!$eProductTranslation){
                    $eProductTranslation = new ProductTranslation();
                    $eProductTranslation->locale = $this->locale;
                }

            }

            if($name !== false) $eProductTranslation->name = $name;

            if($description !== false) $eProductTranslation->description = html_entity_decode((string)str_replace(array('<![CDATA[', ']]>'), '', strip_tags(($description))));


            //Attributes

            if($this->isActualPathSettings('product_attribute', !$creating)){

                $attributes = $this->getElementByPath($product, 'product_attribute', [
                    'notification_type' => 1,
                    'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_attribute'), 'number' => $i + 1 ])
                ]);

                $product_to_attributes = [];

                if(!is_null($attributes)){
                    for($k = 0; $k < $attributes->count(); $k++){

                        $attr = $attributes[$k];

                        $attr_name = $this->getElementByPath($attr, 'product_attribute_name', [
                            'notification_type' => 1,
                            'text' => trans('validation.custom.sync.attribute_name_not_found', ['id' => $extra_1])
                        ]);

                        if(!is_null($attr_name)){
                            $attr_value = $this->getElementByPath($attr, 'product_attribute_name', [
                                'notification_type' => 1,
                                'text' => trans('validation.custom.sync.attribute_value_name_not_found', ['id' => $extra_1 ])
                            ]);

                            if(!is_null($attr_value)){

                                switch ($attr_name) {
                                    case 'Выгружать на сайт':
                                    case 'Код товара для ИМ':
                                    case 'Выгружать на Розетку':
                                    case 'Выгружать на хотлайн':
                                    case 'Выгружать для дилеров':
                                    case 'Розетка код сайта':
                                    case 'Файлы':
                                    case 'Реквизиты':
                                    case 'Ставки налогов':
                                    case 'Базовая единица':
                                    case 'Новые продукты':
                                    case 'Популярные товары':
                                    case 'Название лейбы':
                                    case 'Цена лейба':
                                    case 'Категория':
                                        break;
                                    case 'Класс энергопотербления':
                                        $attr_name = 'Класс энергопотребления';
                                    default:

                                        $attr_name = (string)$attr_name;

                                        $attr_value = (string)$attr_value;

                                        $attribute = $this->getAttributeOrCreate(trim($attr_name));

                                        $attribute_value = $this->getAttributeValueOrCreate($attribute, trim($attr_value));

                                        $attribute_to_product = new AttributeToProduct();

                                        $attribute_to_product->attribute_id = $attribute->id;
                                        $attribute_to_product->main = $k < 6;
                                        $attribute_to_product->has_variant = false;

                                        $attribute_value_to_product = new AttributeValueToProduct();
                                        $attribute_value_to_product->attribute_value_id = $attribute_value->id;
                                        $attribute_value_to_product->attribute_to_product_id = $attribute_to_product->id;

                                        $attribute_to_product->to_values[] = $attribute_value_to_product;

                                        $product_to_attributes[] = $attribute_to_product;

                                        break;
                                }

                            }
                        }
                    }
                }

            }else{
                $product_to_attributes = false;
            }


            try{
                DB::transaction(function() use ($creating, $i, $eProduct, $eProductTranslation, $name, $description, $product, $to_compare_categories, $to_categories, $special, $product_to_attributes){

                    $eProduct->save();

                    if($name !== false || $description !== false){

                        if($name !== false) $eProductTranslation->name = trim($name);
                        if($description !== false) $eProductTranslation->description = $description;

                        $eProductTranslation->product_id = $eProduct->id;
                        $eProductTranslation->save();
                    }

                    if($to_categories !== false){
                        $eProduct->to_categories()->delete();
                        $eProduct->to_compare_categories()->delete();

                        $eProduct->to_categories()->saveMany($to_categories);
                        $eProduct->to_compare_categories()->saveMany($to_compare_categories);
                    }

                    if($special !== false){
                        $eProduct->specials()->delete();
                        $eProduct->specials()->save($special);
                    }

                    if($product_to_attributes !== false) {

                        $eProduct->to_attributes()->delete();

                        foreach ($product_to_attributes as $to_attribute) {

                            $eProduct->to_attributes()->save($to_attribute);

                            $to_attribute->to_values()->saveMany($to_attribute->to_values);
                        }

                    }

                    //Images

                    if($this->isActualPathSettings('product_images', !$creating)){

                        $images_to_product = [];

                        $images = $this->getElementByPath($product, 'product_images', [
                            'notification_type' => 1,
                            'text' => trans('validation.custom.xml-path-incorrect-in-row', ['path' => trans('validation.sync_paths.xml.product_images'), 'number' => $i + 1 ])
                        ]);

                        if(!is_null($images) && $images){
                            for($k = 0; $k < $images->count(); $k++){

                                $image = $images[$k];

                                if($k === 0){
                                    $eProduct->image = $this->getImagePath((string)$image, $eProduct);
                                    $eProduct->update();
                                }else{
                                    $image_to_product = new ImageToProduct();

                                    $image_to_product->sort_order = $k + 1;
                                    $image_to_product->src = $this->getImagePath((string)$image, $eProduct);

                                    $images_to_product[] = $image_to_product;
                                }
                            }
                        }

                        $eProduct->images()->delete();

                        $eProduct->images()->saveMany($images_to_product);

                    }

                });
            }catch (\Throwable $error){
                try{
                    DB::rollBack();
                    \Log::error($error->getMessage());
                }catch (\Exception $exception){
                    \Log::error($exception->getMessage());
                }
            }

            $this->sync->current++;
            $this->sync->success_products_count++;

            $this->sync->update();

            if ($this->sync->current === $this->sync->total) {

                $this->sync->type = null;
                $this->sync->save();

                break;
            }

            $this->sync->refresh();

            if ($this->sync->breaked) break;

            /*

                        if (isset($product->options)) {
                            $options = [];

                            foreach ($offer->options->option as $option) {

                                if (!isset($options[(string)$option['name']])) {
                                    $options[trim((string)$option['name'])] = [
                                        'name' => trim((string)$option['name']),
                                        'product_option_value' => [

                                        ]
                                    ];
                                }

                                $options[trim((string)$option['name'])]['product_option_value'][] = [
                                    'name' => trim((string)$option),
                                    'quantity' => (int)$option['quantity'],
                                    'subtract' => true,
                                    'price' => 0,
                                    'price_prefix' => '+'
                                ];
                            }
                        }
            */

        }

        if ($this->sync->breaked) $this->break();

        $this->sync->type = null;
        $this->sync->finished = true;
        $this->sync->save();

    }

    private function getCategoryId($extra)
    {
        if(isset($this->relation_categories[$extra]) && $this->relation_categories[$extra]){

            $category_id = Category::where('id', $this->relation_categories[$extra])->value('id');

            if(!$category_id){
                return Category::where([
                    ['extra_1', $extra],
                    ['extra_2', $this->extra_2]
                ])->value('id');
            }
        }else{
            return Category::where([
                ['extra_1', $extra],
                ['extra_2', $this->extra_2]
            ])->value('id');
        }
    }

    private function getProductId($extra)
    {
        return Product::where([
            ['extra_1', $extra],
            ['extra_2', $this->extra_2]
        ])->value('id');
    }

    private function getImagePath($image_url, $product)
    {
        $image_url = str_replace(' ', '%20', $image_url);

        $newSrc = str_replace(array('https://wwww.', 'https://wwww.', 'https://', 'https://'), '', $image_url);

        $directories = explode('/', $newSrc);

        $image_name_with_ext = trim(array_pop($directories));

        list($image_name, $image_ext) = explode('.', $image_name_with_ext);

        $new_image_name = md5($image_name) . '.' . $image_ext;

        if ($new_image_name) {

            if (!file_exists(\Storage::disk('uploads')->exists("images/products/{$product->id}/{$new_image_name}"))) {


                $ch = curl_init(trim($image_url));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $exImg = curl_exec($ch);

                if ($exImg) {
                    \Storage::disk('uploads')->put("images/products/{$product->id}/{$new_image_name}", $exImg);
                }

                curl_close($ch);

            };
        }

        $image_url = $new_image_name;

        return $image_url;
    }

    private function getGroupCategories(?int $category_id): array
    {
        $ancestors = [];

        if ($category_id) {

            $ancestors = Category::ancestorsOf($category_id)->map(function ($ancestor) {
                return $ancestor->id;
            })->toArray();

            $ancestors[] = $category_id;
        }

        return $ancestors;
    }

    private function getAttributeOrCreate(string $attribute_name): Attribute
    {
        $attribute = AttributeTranslation::with('attribute')->where([
                'locale' => $this->locale,
                'name' => $attribute_name
            ])->first()->attribute ?? false;

        if (!$attribute) {

            $attribute = new Attribute();
            $attribute->status = false;
            $attribute->slug = \Str::slug($attribute_name, '-', $this->locale);


            $attribute_translation = new AttributeTranslation();
            $attribute_translation->locale = $this->locale;
            $attribute_translation->name = $attribute_name;

            $attribute->save();
            $attribute->translates()->save($attribute_translation);

            $attribute->status = true;
            $attribute->save();
        }

        return $attribute;
    }

    private function getAttributeValueOrCreate(Attribute $attribute, $attribute_value_name): AttributeValue
    {
        $attribute->load('values.translates');

        $attribute_value = $attribute->values->first(function ($value) use ($attribute_value_name) {
            return $value->translates->first(function ($translate) use ($attribute_value_name) {
                return strtolower($translate->value) === strtolower($attribute_value_name);
            });
        });

        if (!$attribute_value) {

            $attribute_value = new AttributeValue();

            $attribute_value->attribute_id = $attribute->id;
            $attribute_value->slug = \Str::slug($attribute_value_name, '-', $this->locale);
            $attribute_value->status = false;

            $attribute_value_translation = new AttributeValueTranslation();

            $attribute_value_translation->locale = $this->locale;
            $attribute_value_translation->value = $attribute_value_name;

            $attribute_value->save();
            $attribute_value->translates()->save($attribute_value_translation);

            $attribute_value->status = true;
            $attribute_value->save();

        }

        return $attribute_value;
    }

    private function break()
    {
        \DB::connection('system')->table('jobs')->where('id', $this->sync->job_id)->delete();
    }

    private function increaseWarning($text)
    {
        $this->sync->warnings_count++;

        $this->sync->update();

        Storage::disk('uploads')->append($this->sync->log_file_path, $text);
    }

    private function setFatalError($text, $notify_support = false)
    {
        if ($this->sync) {

            $this->sync->fatal_error = true;
            $this->sync->update();

            Storage::disk('uploads')->append($this->sync->log_file_path, $text);

            $this->break();
        }

        if ($notify_support) $this->notifySupport($text);

        exit();
    }

    private function notifySupport($text)
    {
        \Log::error("SyncId:{$this->sync_id}, message: $text");
    }

    private function getElementByPath($content, $path_code, $error_info = [])
    {
        $has_error = false;

        try{
            $values = $this->configuration_paths->{$path_code};
        }catch (\ErrorException $e) {
            $values = [];
        }

        $error_text = $error_info['text'] ?? trans('validation.custom.xml-path-incorrect', ['path' => trans('validation.sync_paths.xml.'.$path_code)]);

        $error_notification_type = $error_info['notification_type'] ?? 0;

        if($values){
            foreach ($values as $value){
                if (isset($content->{$value})) {
                    $content = $content->{$value};
                }elseif (isset($content[$value])){
                    $content = $content[$value];
                } else{

                    $has_error = true;

                    if(!$error_notification_type) $error_notification_type = 1;

                    break;
                }
            }
        }else{

            $paths_settings = $this->configuration_creating_updating;

            if( $content->children()->count() && isset($paths_settings->{$path_code}) && ($paths_settings->{$path_code}->creating || $paths_settings->{$path_code}->updating)){
                $has_error = true;
            }elseif($content->children()->count() && $error_notification_type){
                $has_error = true;
            }else{
                return $content;
            }
        }

        if($has_error){
            switch ($error_notification_type){
                case 1:
                    $this->increaseWarning($error_text);
                    break;
                case 2:
                    $this->setFatalError($error_text);
                    break;
            }

            return null;

        }else{

            return $content;

        }


    }

    private function isActualPathSettings(string $path_type, ?int $type)
    {
        /*
         * @$type - оновлення або створення
         * 1 - оновлення
         * 0 - створення
         */

        $is_actual = false;

        if(!is_null($type)){
            $type = $type === 0 ? 'creating' : 'updating';
        }

        try{
            if(is_null($type)){
                $is_actual = $this->configuration_creating_updating->{$path_type}->creating ||  $this->configuration_creating_updating->{$path_type}->updating;
            }else{
                $is_actual = $this->configuration_creating_updating->{$path_type}->{$type};
            }
        }catch (\ErrorException $error){
            $this->setFatalError(trans('validation.custom.configuration-is-broken', ['param' => $path_type]));
        }

        return $is_actual;
    }

    private function deleteOldProducts()
    {
        $products_ids_in_files = [];

        $product_ids_on_site = Product::select('id')->where('extra_2', $this->extra_2)->pluck('id')->toArray();

        for($i = 0; $i < $this->products_count; $i++){

            $product = $this->products[$i];

            $extra_1 = (string)$this->getElementByPath($product, 'product_id', [
                'notification_type' => 1,
                'text' => trans('validation.custom.sync.product_id_not_found', ['number' => $i + 1])
            ]);

            if(!$extra_1) continue;

            if($id = $this->getProductId($extra_1)) $products_ids_in_files[] = $id;
        }

        Product::find(array_diff($product_ids_on_site, $products_ids_in_files))->each->delete();

    }

    private function clearSync($sync)
    {
        $sync->current = 0;
        $sync->total = null;
        $sync->success_products_count = 0;
        $sync->success_categories_count = 0;
        $sync->warnings_count = 0;
        $sync->fatal_error = false;
        $sync->finished = false;
        $sync->type = null;

        Storage::disk('uploads')->delete($sync->log_file_path);

        $sync->update();
    }

}