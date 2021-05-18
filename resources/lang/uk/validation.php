<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'Поле :attribute должно быть от :min до :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'Поле :attribute должно содержать от :min до :max символов.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'Подтвеждение поля :attribute не совпадает.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'Поле :attribute должно содержать от :min до :max знаков.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'Email адрес должен быть валидным.',
    'exists' => 'Выбранный :attribute неправильный.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'Поле :attribute не може бути більше ніж :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'Поле :attribute не должно содержать больше :max символов.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'Файл должен соответсотвовать одному из значений: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'Поле :attribute має містити мінімум :min символів.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'Поле :attribute обязательное к заполнению.',
    'required_if' => 'Поле :attribute обязательное к заполнению, если поле :other ровно :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'Поле :attribute обязательное к заполнению, если не заполнено поле :values.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'Данные :attribute должны быть уникальны.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'Невалидная ссылка :attribute.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'name' => [
            'required' => 'Наименование обязательно к заполнению',
            'max' => 'Максимальное количество символов для наименования :max'
        ],
        'login' => [
            'format' => 'Некорректно введен логин.',
            'unique' => 'Указанный Email/Телефон уже используется.',
            'not_exist' => 'Неверно указан логин или пароль.'
        ],
        'telephone' => [
            'format' => 'Некорректно указанный телефон'
        ],
        'product_type' => 'Вариативной товар всегда должен иметь созданные варианты перед сохранением',
        'inactive_main_language' => 'Нельзя отключить язык, который используется как главный',
        'inactive_main_location' => 'Нельзя отключить или удалить локацию, которая используется как главная',
        'inactive_main_user_group_id' => 'Нельзя отключить или удалить группу пользователей, которая используется как главная',
        'remove_system_user_group_id' => 'Нельзя удалить системную группу пользователей',
        'attribute_or_value_variation_main_translate_not_found' => 'Нельзя включить товар с вариантами, если один из атрибутов или их значений, на которых строятся варианты не переведены на главный язык',
        'inactive_main_order_status_id' => 'Нельзя отключить или удалить статус заказа, который используется как главный',
        'inactive_main_currency' => 'Нельзя отключить или удалить валюту, котороая используется как главная',
        'order_status_used_in_order' => 'Нельзя отключить или удалить статус заказа, если он используется в заказах',
        'inactivate_attribute_in_variants' => 'Нельзя отключить или удалить характеристику, если она используется в вариативных товарах',
        'inactivate_attribute__value_in_variants' => 'Нельзя отключить или удалить значение характеристики, если оно используется в вариативных товарах',
        'translate' => [
            'required' => [
                'name' => 'Наименование для языка :language обязательно к заполнению',
                'value' => 'Значение для языка :language обязательно к заполнению'
            ],
            'max' => [
                'name' => 'Для языка :language в наименовании превышено максимально(:max) допустимое количество символов',
                'meta_h1' => 'Для языка :language в H1 заголовке превышено максимально(:max) допустимое количество символов',
                'meta_title' => 'Для языка :language в мета-заголовке превышено максимально(:max) допустимое количество символов',
                'meta_description' => 'Для языка :language в мета-описание превышено максимально(:max) допустимое количество символов',
                'meta_keywords' => 'Для языка :language в мета-ключах превышено максимально(:max) допустимое количество символов',
                'warranty' => 'Для языка :language в гарантии превышено максимально(:max) допустимое количество символов',
                'postfix' => 'Для языка :language в постфиксе превышено максимально(:max) допустимое количество символов',
                'summary' => 'Для языка :language в кратком описании превышено максимально(:max) допустимое количество символов',
                'value' => 'Для языка :language в значении превышено максимально(:max) допустимое количество символов'
            ],
            'between' => [
                'name' => 'Для языка :language в наименовании должно быть от :min до :max символов',
            ],
            'digits_between' => [

            ]
        ],
        'translates.*.locale' => 'Не выбрана локаль, попробуйте еще раз или обратитесь к администратору сайта',
        'sort_order' => [

        ],
        'variants-number' => 'Вариант под номером :number_variant',
        'for-variant-number' => 'для варианта под номером :number_variant',
        'may-not-create-product-in-tariff' => 'Вы не можете создать больше товаров согласно вашему тарифу',
        'file-excel-is-not-readable' => 'Невозможно прочитать файл, пересохраните его на компютере и попробуйте снова',
        'file-xml-with-errors' => 'Файл содержит синтаксические ошибки, исправьте их и попробуйте снова',
        'have_not_access' => 'У вас нет доступа',
        'xml-path-incorrect' => 'Путь к :path в конфигурации указан неправильно',
        'xml-path-incorrect-in-row' => 'Путь к :path в строке :number не найден',
        'file-can-not-read' => 'Невозможно прочитать файл',
        'configuration-not-exists' => 'Конфигурация не найдена',
        'configuration-is-broken' => 'Конфигурация поломанная, удалите ее и попробуйте снова, или обратитесь к поддержке. Обратите внимание на параметр :param!',
        'can-not-delete-configuration-if-used' => 'Невозможно удалить конфигурацию, которая используется в синхронизации',
        'sync' => [
            'product_id_not_found' => 'Уникальный идентификатор для товара в строке №:number не найден',
            'category_id_not_found' => 'Уникальный идентификатор для категории в строке №:number не найден',
            'parent_category_id_not_found' => 'Уникальный идентификатор родительской категории в строке №:number не найден',
            'product_name_not_found' => 'Название товара отсутствует в строке №:number',
            'category_name_not_found' => 'Название категории отсутствует в строке №:number',
            'currency_not_found' => 'Валюта(:currency_code) указанная в товаре с уникальным идентификатором :id не создана или отключена',
            'attribute_name_not_found' => 'Найдена характеристика без названия, указанная в товаре с уникальным идентификатором :id',
            'attribute_value_name_not_found' => 'Найдено значение характеристики без названия, указанное в товаре с уникальным идентификатором :id',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'password' => 'пароль',
        'firstname' => 'имя',
        'lastname' => 'фамилия',
        'review' => 'отзыв',
        'rating' => 'рейтинг',
        'message' => 'сообщение',
        'telephone' => 'телефон',
        'sort_order' => 'порядок сортировки',
        'slides.*.sort_order' => 'порядок сортировки в слайде',
        'slug' => 'чпу',
        'model' => 'код товара',
        'stock_status_id' => 'статус товара при отсутствии остатков на складе',
        'price' => 'цена',
        'vendor_price' => 'цена',
        'quantity' => 'остатки на складе',
        'currency_code' => 'код валюты',
        'group_id' => 'группа пользователя',
        'minimum' => 'минимальное количество',
        'specials.*.price' => 'цена скидки',
        'special-price' => 'цена скидки',
        'discounts.*.price' => 'оптовая цена',
        'discount-price' => 'оптовая цена',
        'specials.*.user_group_id' => 'группа пользователей(скидки)',
        'special-user_group_id' => 'группа пользователей(скидки)',
        'discounts.*.user_group_id' => 'группа пользователей(оптовые цены)',
        'discount-user_group_id' => 'группа пользователей(оптовые цены)',
        'discounts.*.quantity' => 'количество(оптовые цены)',
        'discount-quantity' => 'количество(оптовые цены)',
        'symbol' => 'символ',
        'locale' => 'локализация',
        'values.*.image' => 'изображение значения',
        'values.*.translates.*.value' => 'наименование для каждого значения и для каждого языка',
        'values.*.status' => 'статус для каждого значения',
        'values.*.slug' => 'ЧПУ для каждого значения',
        'translates.*.name' => 'наименование для каждого языка',
        'configuration_id' => 'конфигурация'
    ],

    'values' => [
        'mimetypes' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'EXCEL'
        ]
    ],

    'sync_paths' => [
        'xml' => [
            'categories_container' => 'контейнеру категорий',
            'category_tag' => 'тегу категории',
            'category_name' => 'названию категории',
            'category_id' => 'уникальному индентификатору категории',
            'category_parent_id' => 'уникальному индентификатору родительской категории',
            'products_container' => 'контейнеу товаров',
            'product_tag' => 'тегу товара',
            'product_id' => 'уникальному индентификатору товара',
            'product_quantity' => 'количеству',
            'product_price' => 'цене',
            'product_discount' => 'скидке',
            'product_currency' => 'валюте',
            'product_category_id' => 'категории',
            'product_sku' => 'артикулу товара',
            'product_images' => 'изображению',
            'product_name' => 'названию',
            'product_slug' => 'ЧПУ товара',
            'product_attribute' => 'характеристике',
            'product_description' => 'описанию товара',
            'product_attribute_name' => 'наименованию характеристики',
            'product_attribute_value' => 'значению характеристики',
            'site_source' => 'названию источника данных'
        ]
    ]

];
