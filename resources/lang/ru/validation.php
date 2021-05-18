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
        'numeric' => 'Поле :attribute має бути від :min до :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'Поле :attribute має містити від :min до :max символів.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'Підтвердження поля :attribute не співпадає.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'Поле :attribute має містити від :min до :max знаків.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'Email адреса має бути валідною.',
    'exists' => 'Вибраний :attribute є неправильний.',
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
    'integer' => 'The :attribute must be an integer.',
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
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'Поле :attribute має містити мінімум :min символів.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'Поле :attribute є обов\'язкове для заповнення.',
    'required_if' => 'Поле :attribute обов\'язкове для заповнення, якщо поле :other рівне :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
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
    'unique' => 'Дані :attribute мають бути унікальні.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'Невалідне посилання :attribute.',
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
        'login' => [
            'format' => 'Некоректно введений логін.',
            'unique' => 'Вказаний Email/Телефон вже використовується.',
            'not_exist' => 'Невірно вказаний логін або пароль.'
        ],
        'telephone' => [
            'format' => 'Некоректно вказаний телефон'
        ],
        'product_type' => 'Варіативний товар завжди має мати створені варіанти перед збереженням',
        'inactive_main_language' => 'Не можна відключити мову, яка визначена як основна',
        'inactive_main_location' => 'Не можна відключити або видалити локацію, яка визначена як основна',
        'inactive_main_user_group_id' => 'Не можна відключити або видалити групу користувача, яка визначена як основна',
        'remove_system_user_group_id' => 'Не можна видалити системну групу користувачів',
        'attribute_or_value_variation_main_translate_not_found' => 'Не можна включити товар з варіантами, якщо один із атрибутів або їх значень, на яких будуються варіанти не перекладені на основну мову',
        'inactive_main_order_status_id' => 'Не можна відключити або видалити статус замовлення, який визначений як головний',
        'inactive_main_currency' => 'Не можна відключити або видалити валюту, яка визначена головною',
        'inactivate_attribute_in_variants' => 'Не можна відключити або видалити характеристику, якщо вона використовується в варіативних товарах',
        'inactivate_attribute__value_in_variants' => 'Не можна відключити або видалити значення характеристики, якщо воно використовується в варіативних товарах',
        'order_status_used_in_order' => 'Не можна відключити або видалити статус замовлення, якщо він використовується в замовленнях',
        'translate' => [
            'required' => [
                'name' => 'Найменування для мови :language обов\'язкове до заповнення',
                'value' => 'Значення для мови :language обов\'язкове до заповнення'
            ],
            'max' => [
                'name' => 'Для мови :language в найменуванні перевищена максимально(:max) допустима кількість символів',
                'meta_h1' => 'Для мови :language в H1 заголовку перевищена максимально (max) допустима кількість символів',
                'meta_title' => 'Для мови :language в мета-заголовку перевищена максимально (max) допустима кількість символів',
                'meta_description' => 'Для мови :language в мета-опис перевищена максимально (max) допустима кількість символів',
                'meta_keywords' => 'Для мови :language в мета-ключах перевищена максимально (max) допустима кількість символів',
                'warranty' => 'для мови :Language в гарантії перевищена максимально(:max) допустима кількість символів',
                'postfix' => 'Для мови :language в постфиксе перевищена максимально (max) допустима кількість символів',
                'summary' => 'Для мови :language в короткому описі перевищена максимально (max) допустима кількість символів',
                'value' => 'Для мови :language в значенні перевищено максимальна (max) допустима кількість символів'
            ],
            'between' => [
                'name' => 'Для мови :language в найменуванні має бути від :min до :max символів',
            ],
            'digits_between' => [

            ]
        ],
        'translates.*.locale' => 'Не вибрано локаль, спробуйте ще раз або зверніться до адміністратора сайту',
        'sort_order' => [

        ],
        'variants-number' => 'Варіант під номером :number_variant',
        'for-variant-number' => 'для варіанту під номером :number_variant',
        'file-excel-is-not-readable' => 'Неможливо прочитати файл, перезбережіть його на комп\'ютері та спробуйте ще раз',
        'file-xml-with-errors' => 'Файл мість синтаксичні помилки, усуньте їх та спробуйте ще раз',
        'file-can-not-read' => 'Неможливо прочитати файл',
        'xml-path-incorrect' => 'Шлях до :path в конфігурації вказаний неправильно',
        'may-not-create-product-in-tariff' => 'Ви не можете створити більше товарів згідно вашого тарифу',
        'xml-path-incorrect-in-row' => 'Шлях до :path в рядку :number не знайдений',
        'have_not_access' => 'У вас не має доступу',
        'configuration-not-exists' => 'Конфігурація не знайдена',
        'configuration-is-broken' => 'Конфігурація поломана, видаліть її та спопробуйте знову, або зверніться до підтримки. Зверніть увагу на параметр :param!',
        'can-not-delete-configuration-if-used' => 'Неможливо видалити конфігурацію, яка використовується в синхронізації',
        'sync' => [
            'product_id_not_found' => 'Унікальний індентифікатор для товару в рядку №:number не знайдений',
            'category_id_not_found' => 'Унікальний індентифікатор для категорії в рядку №:number не знайдений',
            'parent_category_id_not_found' => 'Унікальний індентифікатор батьківської категорії в рядку №:number не знайдений',
            'product_name_not_found' => 'Назва товару відсутня в рядку №:number',
            'category_name_not_found' => 'Назва категорії відсутня в рядку №:number',
            'currency_not_found' => 'Валюта(:currency_code) вказана в товарі з унікальним індентифікатором :id не створена або відключена',
            'attribute_name_not_found' => 'Знайдена характеристика без назви, вказана в товарі з унікальним індентифікатором :id',
            'attribute_value_name_not_found' => 'Знайдено значення характеристики без назви, вказане в товарі з унікальним індентифікатором :id',
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
        'firstname' => 'ім\'я',
        'lastname' => 'прізвище',
        'review' => 'відгук',
        'rating' => 'рейтинг',
        'message' => 'повідомлення',
        'telephone' => 'телефон',
        'sort_order' => 'порядок сортування',
        'slug' => 'чпу',
        'model' => 'код товару',
        'stock_status_id' => 'статус товару при відсутності залишків на складі',
        'price' => 'ціна',
        'quantity' => 'залишки на складі',
        'currency_code' => 'код валюти',
        'group_id' => 'група користувача',
        'minimum' => 'мінімальна кількість',
        'specials.*.price' => 'ціна знижки',
        'special-price' => 'ціна знижки',
        'discounts.*.price' => 'оптова ціна',
        'discount-price' => 'оптова ціна',
        'specials.*.user_group_id' => 'група користувачів(знижки)',
        'special-user_group_id' => 'група користувачів(знижки)',
        'discounts.*.user_group_id' => 'група користувачів(оптові ціни)',
        'discount-user_group_id' => 'група користувачів(оптові ціни)',
        'discounts.*.quantity' => 'кількість(оптові ціни)',
        'discount-quantity' => 'кількість(оптові ціни)',
        'symbol' => 'символ',
        'locale' => 'локалізація',
        'configuration_id' => 'конфігурація'

    ],

    'sync_paths' => [
        'xml' => [
            'categories_container' => 'контейнеру категорій',
            'category_tag' => 'тегу категорії',
            'category_name' => 'назви категорії',
            'category_id' => 'унікального індентифікатора категорії',
            'category_parent_id' => 'унікального індентифікатора батьківської категорії',
            'products_container' => 'контейнеру товарів',
            'product_tag' => 'тегу товару',
            'product_id' => 'унікального індентифікатора товару',
            'product_quantity' => 'кількості товару',
            'product_price' => 'ціни товару',
            'product_discount' => 'знижки товару',
            'product_currency' => 'валюти товару',
            'product_category_id' => 'категорії товару',
            'product_sku' => 'артикулу товару',
            'product_images' => 'зображення товару',
            'product_name' => 'назви товару',
            'product_slug' => 'ЧПУ товару',
            'product_description' => 'опису товару',
            'product_attribute' => 'характеристики',
            'product_attribute_name' => 'назви характеристики',
            'product_attribute_value' => 'значення характеристики',
            'site_source' => 'назви джерела даних'
        ]
    ]

];
