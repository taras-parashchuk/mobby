<?php
return [
    'headers' => [
        'new_order' => 'Спасибо за заказ!',
        'received_fast_order' => 'Вы получули быстрый заказ от :telephone',
        'new_callback' => 'Вы получили новое обращение из сайта :type',
        'new_testimonial' => 'Вам написали новый отзыв на сайте',
        'update_order' => 'Обновление информации о вашем заказе №:order_id'
    ],
    'columns' => [
        'order_date_added' => 'Дата создания',
        'payment_method' => 'Способ оплаты',
        'shipping_method' => 'Способ доставки',
        'address' => 'Адрес',
        'user_contacts' => 'Контактные данные',
        'comment' => 'Комментарий',
        'products' => 'Список товаров',
        'name' => 'Наименование',
        'model' => 'Код товара',
        'quantity' => 'Количество',
        'specification' => 'Спецификация',
        'price' => 'Цена',
        'price_total' => 'Стоимость',
        'total' => 'Всего',
        'customer_name' => 'Им\'я',
        'customer_email' => 'Email',
        'customer_telephone' => 'Телефон',
    ],
    'fast_order' => [
        'subtitle' => 'Вы получили быстрый заказ. Номер телефона клиента: :telephone',
        'product_text' => 'Ссылка на товар - <a href=":href">:name</a>'
    ],
    'callback' => [
        'customer_message' => 'Обращение',
        'prefer_callback_time' => 'Cв\'язаться через :time'
    ],
    'testimonial' => [

    ],
    'update_order' => [
        'your_order_status' => 'Ваш статус заказа: :status'
    ]
];