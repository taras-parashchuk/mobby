<?php

return [
    'errors' => [
        'product' => [
            'not_exist' => 'Товар :name не синхронизирован',
            'currency_not_exist' => 'Невозможно загрузить товар :name, указанной валюты не существует',
            'images_not_uploaded' => 'Возникла ошибка, изображение для товара :name не выгружены',
            'image_not_downloaded' => 'Возникла ошибка, изображение для товара :name не загружено',
            'product_not_uploaded' => 'Возникла ошибка, товар №:id :name не выгружен',
            'product_not_download' => 'Возникла ошибка, товар  :product_url не загружен',
            'data_type_is_not_supported' => 'Тип данных :data_type не поддерживает',
        ],
        'category' => [
            'not_exist' => 'Категории товара не существует на Мой Склад, товар :id. :name загружен без категории',
            'not_related' => 'Для товара: id не создано категорию https://online.moysklad.ru/app/#good/edit?id=:category_id, поэтому связать невозможно',
        ],
        'order' => [
            'external_product_not_exist' => 'Товар :product_url с заказ :order_id не загружен, он не синхронизирован',
            'product_not_exist' => 'Товара с заказа №:id не существует',
            'external_counterparty_not_exist' => 'Пользователя  не существует на Мой Склад, заказ №:id не загружен',
            'error' => 'Возникла ошибка, товар №:product с заказ №:order не загруженный',
            'user_not_related' => 'Пользователь не имеет связи между Мой Склад и сайтом. Заказ №:id загружен без данных пользователя.',
        ],
        'file_not_exist' => 'Файла с таким типом данных не существует.',
        'unknown_error' => 'Возникла неизвестная ошибка.',
        'unknown_error_with_details' => 'Возникла неизвестная ошибка. Синхронизация остановлена на :id :name',
    ]
];
