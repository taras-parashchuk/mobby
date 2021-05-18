<?php

namespace App\Helpers;

use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Setting;

class HelperFunction
{
    private static $theme;

    public static function get_seo_template_or_default($type, $meta, $default, $extra = [])
    {
        $seo_templates = json_decode(Setting::get('seo_templates', '', 'seo'));

        if ($seo_templates && $seo_templates->status) {

            $template = collect($seo_templates->templates->{$type}->translates)->firstWhere('locale', app()->getLocale())->{$meta};

            if ($type !== 'product') {
                return str_replace(['<H1>'], [$default], html_entity_decode($template));
            } else {

                if ($extra['special']) {
                    $seo_price = $extra['special'];
                } else {
                    $seo_price = $extra['price'];
                }


                $heading_title = str_replace(
                    ['<H1>', '<PRICE>', '<SKU>'],
                    [$default, $seo_price, $extra['model']],
                    html_entity_decode($template));

                preg_match_all('#\<ATTR_([0-9]+)\>#ui', $heading_title, $mask_attrs);

                if (isset($mask_attrs[1])) {
                    foreach ($mask_attrs['1'] as $attribute_id) {
                        foreach ($extra['attributes'] as $attribute) {
                            if ($attribute->attribute_id == $attribute_id) {
                                $heading_title = preg_replace('#\<ATTR_([0-9]+)\>#ui', $attribute->attribute->translate->name . ' - ' . implode(',', $attribute->values->pluck('translate.value')->toArray()), $heading_title, 1);
                                break;
                            }
                        }
                    }
                }

                return str_replace('<ATTR_#>', '', $heading_title);

            }

        } else {
            return $default;
        }
    }

    public static function getAttributeOrCreate(string $attribute_name, string $locale = null): Attribute
    {
        if(is_null($locale)){
            $locale = Setting::get('site_language');
        }

        $attribute = AttributeTranslation::with('attribute')->where([
                'locale' => $locale,
                'name' => $attribute_name
            ])->first()->attribute ?? false;

        if (!$attribute) {

            $attribute = new Attribute();
            $attribute->status = false;
            $attribute->slug = \Str::slug($attribute_name, '-', $locale);


            $attribute_translation = new AttributeTranslation();
            $attribute_translation->locale = $locale;
            $attribute_translation->name = $attribute_name;

            $attribute->save();
            $attribute->translates()->save($attribute_translation);

            $attribute->status = true;
            $attribute->save();
        }

        return $attribute;
    }

    public static function getAttributeValueOrCreate(Attribute $attribute, $attribute_value_name, string $locale = null): AttributeValue
    {

        if(is_null($locale)){
            $locale = Setting::get('site_language');
        }

        $attribute->load('values.translates');

        $attribute_value = $attribute->values->first(function ($value) use ($attribute_value_name) {
            return $value->translates->first(function ($translate) use ($attribute_value_name) {
                return strtolower($translate->value) === strtolower($attribute_value_name);
            });
        });

        if (!$attribute_value) {

            $attribute_value = new AttributeValue();

            $attribute_value->attribute_id = $attribute->id;
            $attribute_value->slug = \Str::slug($attribute_value_name, '-', $locale);
            $attribute_value->status = false;

            $attribute_value_translation = new AttributeValueTranslation();

            $attribute_value_translation->locale = $locale;
            $attribute_value_translation->value = $attribute_value_name;

            $attribute_value->save();
            $attribute_value->translates()->save($attribute_value_translation);

            $attribute_value->status = true;
            $attribute_value->save();

        }

        return $attribute_value;
    }
}