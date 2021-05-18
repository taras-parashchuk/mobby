<?php


namespace App\Helpers\Excel\Imports;

use App\Helpers\FileManager;
use App\Helpers\Image;
use App\Models\Attribute;
use App\Models\AttributeToProduct;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueToProduct;
use App\Models\AttributeValueTranslation;
use App\Models\Category;
use App\Models\ImageToProduct;
use App\Models\Language;
use App\Models\PriceUnit;
use App\Models\PriceUnitTranslation;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductSpecial;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\ProductVariantAttributeValue;
use App\Models\Setting;
use App\Models\StockStatus;
use App\Models\StockStatusTranslation;
use App\Models\Sync;
use App\Models\UserGroup;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Exception;
use Str;

use Maatwebsite\Excel\Concerns\OnEachRow;
use UniSharp\LaravelFilemanager\Lfm;

class ExcelImportProducts implements OnEachRow, WithEvents
{

    use RegistersEventListeners;

    private $configuration;
    private $locale;

    private $extra_1;
    private $name;
    private $sku;
    private $quantity;
    private $price_unit;
    private $images;
    private $manufacturer;
    private $minimum;
    private $category_id;
    private $currency_code;
    private $vendor_price;
    private $description;
    private $meta_keywords;
    private $whosale;
    private $whosale_separator = ';';
    private $special;

    private $extra_2;
    private $attributes_pos_start;
    private $with_variants = false;

    private $sync;
    private $total_rows;

    public function __construct(Sync $sync, $configuration, $locale)
    {
        $this->locale = $locale;

        $this->configuration = $configuration;

        $this->sync = $sync;

    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {

                if($this->sync->type !== 'Export Products Sheet'){
                    $this->sync->current = 0;
                    $this->sync->type = 'Export Products Sheet';
                }

                $this->sync->total = $event->sheet->getDelegate()->getHighestDataRow() - 1;

                $this->sync->save();
            },
        ];
    }

    public function onRow(Row $row)
    {

        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        if ($rowIndex === 1 || $this->sync->current > $rowIndex - 1) return;

        $this->fromCustom($row);

        if ($rowIndex > 1) {

            $this->sync->current++;
            $this->sync->success_products_count++;

            $this->sync->update();

            if ($this->sync->current === $this->sync->total) {
                $this->sync->type = null;
            }

            $this->sync->refresh();

            if($this->sync->breaked){
                $this->break();
            }
        }

    }

    private function prepareColumns($row)
    {
        foreach ($this->configuration as $column => $value) {

            if ($column === 'whosale_separator' || $column === 'with_variants' || $column === 'attributes_pos_start') {
                $this->{$column} = $value ?? null;
            } else {
                $this->{$column} = $row[$value] ?? null;
            }

            switch ($column) {
                case 'category_id':
                    $this->category_id = Category::where('extra_1', $row[$value])->first()->id ?? null;
                    break;
                case 'description':
                    $this->description = strip_tags($row[$value] ?? '');
                    break;
                case 'whosale':
                    if($value){
                        $value = explode(',', $value);

                        if ($value) {
                            $this->whosale = [
                                'quantities' => explode($this->whosale_separator, $row[$value[0]]),
                                'prices' => explode($this->whosale_separator, $row[$value[1]])
                            ];
                        } else {
                            $this->whosale = null;
                        }
                    }else{
                        $this->whosale = null;
                    }
                    break;
                case 'images':
                    $this->images = explode(', ', $this->images ?? '');
                    break;
            }
        }
    }

    private function getPriceUnitIdOrCreate(string $price_unit_name): int
    {
        $price_unit_id = PriceUnitTranslation::where([
                'locale' => $this->locale,
                'name' => $price_unit_name
            ])->first()->price_unit_id ?? false;

        if (!$price_unit_id) {

            $price_unit = new PriceUnit();

            $price_unit->display = false;
            $price_unit->status = true;

            $price_unit_translation = new PriceUnitTranslation();
            $price_unit_translation->locale = $this->locale;
            $price_unit_translation->name = $price_unit_name;

            $price_unit->save();
            $price_unit->translates()->save($price_unit_translation);

            $price_unit_id = $price_unit->id;
        }

        return $price_unit_id;
    }

    private function getImagePath($image_url, $product)
    {
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {

            $newSrc = str_replace(array('https://wwww.', 'https://wwww.', 'https://', 'https://'), '', $image_url);

            $directories = explode('/', $newSrc);

            $image_name = trim(array_pop($directories));

            if (!file_exists(\Storage::disk('uploads')->exists("images/products/{$product->id}/{$image_name}"))) {

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
                    \Storage::disk('uploads')->put("images/products/{$product->id}/{$image_name}", $exImg);
                }

                curl_close($ch);

            };

            $image_url = $image_name;

        }

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

    private function getAttributesValuesIds($row)
    {
        $parts = [];

        if (!is_null($this->attributes_pos_start)) {

            $i = (int)$this->attributes_pos_start;

            $k = 0;

            while (isset($row[$i]) && !is_null($row[$i]) && strlen($row[$i])) {

                $attribute = $this->getAttributeOrCreate($row[$i]);

                if(!$attribute){
                    $i += 3; continue;
                }

                $attribute_value_column = $row[$i + 2];

                $attribute_values = explode(';', $attribute_value_column);

                if (count($attribute_values) === 1) {
                    $attribute_value = $this->getAttributeValueOrCreate($attribute, $row[$i + 2]);

                    if(!$attribute_value){
                        $i += 3; continue;
                    };

                    $parts[] = [
                        'attribute_id' => $attribute->id,
                        'attribute_value_id' => $attribute_value->id,
                        'main' => $k < 5,
                        'has_variant' => false
                    ];

                } else {

                    $values = [];

                    foreach ($attribute_values as $value) {

                        $attribute_value = $this->getAttributeValueOrCreate($attribute, $value);

                        if($attribute_value) $values[] = $attribute_value->id;

                    }

                    if(count($values)) {
                        $parts[] = [
                            'attribute_id' => $attribute->id,
                            'attribute_values' => $values,
                            'main' => $k < 5,
                            'has_variant' => false
                        ];
                    }else{
                        $i += 3;
                        continue;
                    }


                }

                $i += 3;
                $k++;
            }
        }

        return $parts;
    }

    private function getAttributeOrCreate(string $attribute_name): ?Attribute
    {
        $attribute = AttributeTranslation::with('attribute')->where([
                'locale' => $this->locale,
                'name' => $attribute_name
            ])->first()->attribute ?? false;

        if ($attribute_name && !$attribute) {

            $attribute = new Attribute();
            $attribute->status = false;
            $attribute->slug = Str::slug($attribute_name, '-', $this->locale);


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

    private function getAttributeValueOrCreate(Attribute $attribute, $attribute_value_name): ?AttributeValue
    {
        $attribute->load('values.translates');

        $attribute_value = $attribute->values->first(function ($value) use ($attribute_value_name) {
            return $value->translates->first(function ($translate) use ($attribute_value_name) {
                return strtolower($translate->value) === strtolower($attribute_value_name);
            });
        });

        if ($attribute_value_name && !$attribute_value) {

            $attribute_value = new AttributeValue();

            $attribute_value->attribute_id = $attribute->id;
            $attribute_value->slug = Str::slug($attribute_value_name, '-', $this->locale);
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

    private function getType($extra_1, $extra_2)
    {
        if ($extra_2) {
            if ($c = Product::where([
                ['extra_2', $extra_2],
                ['extra_1', '<>', $extra_1]
            ])->limit(1)->count()) {
                return 3;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    private function fromCustom($row)
    {
        $this->prepareColumns($row);

        if($this->with_variants){
            $product = Product::where([
                ['extra_1', $this->extra_1],
                ['extra_2', $this->extra_2],
            ])->first();
        }else{
            $product = Product::where('extra_1', $this->extra_1)->first();
        }

        if (!$product) {
            $product = new Product();

            $product->type = 1;
            $product->extra_1 = $this->extra_1;
            $product->extra_2 = $this->extra_2;

        }

        $type = $this->getType($this->extra_1, $this->extra_2);

        if ($type !== 3 || $this->with_variants) {

            if ($this->sku) $product->sku = $this->sku;

            if (is_numeric($this->quantity)) {
                $product->quantity = (float)$this->quantity;
            } elseif ($this->quantity == '-') {
                $product->quantity = 0;
            } elseif ($this->quantity == '+') {
                $product->quantity = 100;
            }

            if ($this->minimum) $product->minimum = $this->minimum;

            $product->currency_code = $this->currency_code;

            if ($this->vendor_price) $product->vendor_price = $this->vendor_price;

            $translation = new ProductTranslation();

            $translation->locale = $this->locale;
            $translation->name = $this->name;
            $translation->description = $this->description;
            $translation->meta_keywords = $this->meta_keywords;

            $discounts = [];

            if ($this->whosale) {
                UserGroup::enabled()->get()->each(function ($u_group) use (&$discounts) {

                    $group_count = count($this->whosale['quantities']);

                    $i = 0;

                    while ($i < $group_count) {

                        if ($this->whosale['quantities'][$i] && $this->whosale['prices'][$i]) {

                            $discount = new ProductDiscount();

                            $discount->user_group_id = $u_group->id;
                            $discount->quantity = $this->whosale['quantities'][$i];
                            $discount->price = $this->whosale['prices'][$i];

                            $discounts[] = $discount;
                        }

                        $i++;
                    }
                });
            }

            $specials = [];

            if ($this->special) {
                UserGroup::enabled()->get()->each(function ($u_group) use (&$specials) {
                    $special = new ProductSpecial();

                    $special->user_group_id = $u_group->id;
                    $special->price = $this->special;

                    $specials[] = $special;
                });

            }

            if ($this->price_unit) $product->price_unit_id = $this->getPriceUnitIdOrCreate($this->price_unit);

            $product->category_id = $this->category_id;

            $categories_list = $this->getGroupCategories($this->category_id);

            $to_compare_categories = $to_categories = [];

            foreach ($categories_list as $category_id) {
                $p_t_compare_category = new ProductToCompareCategory();

                $p_t_compare_category->category_id = $category_id;

                $p_t_category = new ProductToCategory();

                $p_t_category->category_id = $category_id;

                $to_compare_categories[] = $p_t_compare_category;
                $to_categories[] = $p_t_category;
            }

            $product->slug = Str::slug($this->name, '-', $this->locale);

            $product->status = false;

            $product->save();

            $product->translates()->delete();
            $product->translates()->save($translation);

            if ($this->images) {

                $product->image = $this->getImagePath($this->images[0], $product);

                array_shift($this->images);

                $images_to_product = [];

                foreach ($this->images as $sort_order => $image) {
                    $image_to_product = new ImageToProduct();

                    $image_to_product->sort_order = $sort_order;
                    $image_to_product->src = $this->getImagePath($image, $product);

                    $images_to_product[] = $image_to_product;
                }
            }

            $product->save();

            $characteristics = $this->getAttributesValuesIds($row);

            $product->to_categories()->delete();
            $product->to_compare_categories()->delete();

            $product->to_categories()->saveMany($to_categories);
            $product->to_compare_categories()->saveMany($to_compare_categories);

            $product->to_attributes()->delete();

            foreach ($characteristics as $characteristic) {

                $attribute_to_product = new AttributeToProduct();

                $attribute_to_product->attribute_id = $characteristic['attribute_id'];
                $attribute_to_product->main = $characteristic['main'];
                $attribute_to_product->has_variant = $characteristic['has_variant'];

                $product->to_attributes()->save($attribute_to_product);

                if (isset($characteristic['attribute_values'])) {
                    foreach ($characteristic['attribute_values'] as $attribute_value_id) {
                        $attribute_value_to_product = new AttributeValueToProduct();
                        $attribute_value_to_product->attribute_value_id = $attribute_value_id;

                        $attribute_to_product->to_values()->save($attribute_value_to_product);
                    }
                } else {
                    $attribute_value_to_product = new AttributeValueToProduct();
                    $attribute_value_to_product->attribute_value_id = $characteristic['attribute_value_id'];
                    $attribute_value_to_product->attribute_to_product_id = $attribute_to_product->id;

                    $attribute_to_product->to_values()->save($attribute_value_to_product);
                }
            }

            $product->discounts()->delete();
            $product->discounts()->saveMany($discounts);

            $product->specials()->delete();
            $product->specials()->saveMany($specials);

            $product->images()->delete();
            $product->images()->saveMany($images_to_product);

            Product::setPrices($product);

            $product->status = true;

            $product->save();

        }
    }

    private function break()
    {
        \DB::connection('system')->table('jobs')->where('id', $this->sync->job_id)->delete();

        die();
    }
}