<?php

namespace App\Helpers;

use App\Models\Language;

trait CustomValidation
{
    private $languages;

    private $data;

    public function messages($data = [])
    {
        $messages = [];

        $this->languages = Language::getOnlyActive();

        $this->data = $data ?: request()->all();

        foreach ($this->data['translates'] ?? [] as $key => $val) {

            $messages['translates.' . $key . '.value.required'] = trans('validation.custom.translate.required.value', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.value.max'] = trans('validation.custom.translate.max.value', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.name.required'] = $messages['translates.' . $key . '.title.required'] = trans('validation.custom.translate.required.name', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.name.max'] = trans('validation.custom.translate.max.name', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.name.between'] = $messages['translates.' . $key . '.title.between'] = trans('validation.custom.translate.between.name', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.meta_h1.max'] = trans('validation.custom.translate.max.meta_h1', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.meta_title.max'] = trans('validation.custom.translate.max.meta_title', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.meta_description.max'] = trans('validation.custom.translate.max.meta_description', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.meta_keywords.max'] = trans('validation.custom.translate.max.meta_keywords', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.warranty.max'] = trans('validation.custom.translate.max.warranty', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.postfix.max'] = trans('validation.custom.translate.max.postfix', [
                'language' => $this->getLanguageName($key)
            ]);

            $messages['translates.' . $key . '.summary.max'] = trans('validation.custom.translate.max.summary', [
                'language' => $this->getLanguageName($key)
            ]);
        }

        foreach ($this->data['variants'] ?? [] as $variant_number => $variant) {
            foreach ($variant['translates'] as $key => $val) {
                $messages["variants.$variant_number.translates.$key.name.required"] = trans('validation.custom.translate.required.name', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.name.max"] = trans('validation.custom.translate.max.name', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.meta_h1.max"] = trans('validation.custom.translate.max.meta_h1', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.meta_title.max"] = trans('validation.custom.translate.max.meta_title', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.meta_description.max"] = trans('validation.custom.translate.max.meta_description', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.meta_keywords.max"] = trans('validation.custom.translate.max.meta_keywords', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);

                $messages["variants.$variant_number.translates.$key.warranty.max"] = trans('validation.custom.translate.max.warranty', [
                        'language' => $this->getLanguageName($key, $variant['translates'])
                    ]) . '. ' . trans('validation.custom.variants-number', ['number_variant' => $variant_number + 1]);
            }
        }

        return $messages;
    }

    private function getLanguageName($position, $data = [])
    {
        if(!$data) $data = $this->data['translates'];

        return $this->languages->firstWhere('locale', $data[$position]['locale'])->name;
    }


    public function attributes()
    {
        $attributes = [];

        foreach ($this->data['variants'] ?? [] as $number_variant => $variant) {
            $attributes["variants.$number_variant.sku"] = trans('validation.attributes.model') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
            $attributes["variants.$number_variant.vendor_price"] = trans('validation.attributes.vendor_price') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
            $attributes["variants.$number_variant.quantity"] = trans('validation.attributes.quantity') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
            $attributes["variants.$number_variant.slug"] = trans('validation.attributes.slug') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);

            foreach ($variant['specials'] ?? [] as $i => $v){
                $attributes["variants.$number_variant.specials.$i.price"] = trans('validation.attributes.special-price') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
                $attributes["variants.$number_variant.specials.$i.user_group_id"] = trans('validation.attributes.special-user_group_id') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
            }

            foreach ($variant['discounts'] ?? [] as $i => $v){
                $attributes["variants.$number_variant.discounts.$i.quantity"] = trans('validation.attributes.discount-quantity') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
                $attributes["variants.$number_variant.discounts.$i.price"] = trans('validation.attributes.discount-price') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
                $attributes["variants.$number_variant.discounts.$i.user_group_id"] = trans('validation.attributes.discount-user_group_id') . ' ' . trans('validation.custom.for-variant-number', ['number_variant' => $number_variant + 1]);
            }



        }

        return $attributes;
    }
}