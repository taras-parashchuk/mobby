<?php
return [
    'headers' => [
        'new_order' => 'Дякуємо за замовлення!',
        'received_fast_order' => 'Ви отримали швидке замовлення від :telephone',
        'new_callback' => 'Ви отримали нове звернення з сайту - :type',
        'new_testimonial' => 'Вам написали новий відгук на сайті'
    ],
    'columns' => [
        'order_date_added' => 'Дата сторення',
        'payment_method' => 'Спосіб оплати',
        'shipping_method' => 'Спосіб доставки',
        'address' => 'Адреса',
        'user_contacts' => 'Контактні дані',
        'comment' => 'Коментар',
        'products' => 'Список товарів',
        'name' => 'Найменування',
        'model' => 'Код товару',
        'quantity' => 'Кількість',
        'specification' => 'Специфікація',
        'price' => 'Ціна',
        'price_total' => 'Вартість',
        'total' => 'Всього',
        'customer_name' => 'Ім\'я',
        'customer_email' => 'Email',
        'customer_telephone' => 'Телефон',
    ],
    'fast_order' => [
        'subtitle' => 'Ви отримали швидке замовлення. Номер телефону клієнта: :telephone',
        'product_text' => 'Посилання на товар - <a href=":href">:name</a>'
    ],
    'callback' => [
        'customer_message' => 'Звернення',
        'prefer_callback_time' => 'Зв\'язатися через :time'
    ]
];