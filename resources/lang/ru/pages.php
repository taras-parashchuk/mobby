<?php

return [
    'comparelist' => [
        'heading' => 'Порівняння товарів',
        'button' => [
            'all_attr' => 'Всі характеристики',
            'different_attr' => 'Лише різні',
            'same_attr' => 'Лише однакові'
        ],
        'none_category' => 'Без рубрики'
    ],
    'search' => [
        'heading' => [
            'single' => 'Пошук',
            'with_results' => 'Результати по запиту: :search_phrase',
        ],
        'filter' => [
            'all_categories' => 'Пошук у всіх підкатегоріях',
            'include_descriptions' => 'Шукати в описі'
        ]
    ],
    'checkout' => [
        'heading' => 'Оформлення замовлення',
        'sections' => [
            'contact' => [
                'heading' => 'Контактні дані',
                'has_account' => 'Вже реєструвалися?',
                'login' => 'Ввійти',
                'full_name' => 'ПІБ',
                'telephone' => 'Телефон',
                'email' => 'Email',
                'fast_order' => 'Швидка покупка',
                'fast_order_help' => 'Заказ оформляється зразу, всі деталі уточнить менеджер',
                'errors' => [
                    'full_name' => 'ПІБ має містити від 2 до 64 символа',
                    'telephone' => 'Неправильний формат телефону',
                    'email' => 'Некоректний email',
                    'input' => 'Введіть коректне значення',
                ]

            ],
            'shipping' => [
                'heading' => 'Доставка',
                'method' => 'Спосіб доставки',
                'choose_method' => 'Виберіть метод зі списку',
                'errors' => [
                    'method' => 'Виберіть метод зі списку',
                    'input' => 'Введіть коректне значення',
                ]
            ],
            'payment' => [
                'heading' => 'Оплата',
                'method' => 'Спосіб оплати',
                'comment' => 'Коментар',
                'choose_method' => 'Виберіть метод зі списку',
                'errors' => [
                    'method' => 'Виберіть метод зі списку',
                    'input' => 'Введіть коректне значення',
                    'select' => 'Виберіть значення зі списку',
                ],
                'button_confirm' => 'Оформити замовлення'
            ]
        ],
        'confirm' => [
            'fast_order' => 'Створено швидке замовлення через кошик',
            'full_order' => 'Створено замовлення через кошик',
            'online_pay' => 'Замовлення №:order_id'
        ],
        'success' => [
            'heading' => 'Замовлення прийнято',
            'text' => [
                'guest' => '<p>Наш менеджер найближчим часом зв\'яжеться з вами для підтверждення.</p><p>Дякуємо за покупку!</p>',
                'registered' => '<p>Ви можете переглянути історію Ваших замовлень перейшовши в <a class="text-link" href=":account">Мій обліковий запис</a> і натиснувши на <a class="text-link" href=":order-history">Історія замовлень</a>.</p><p>Наш менеджер найближчим часом зв\'яжеться з вами для підтверждення.</p><p>Дякуємо за покупку!</p>'
            ]
        ]
    ],
    'blog' => [
        'heading' => 'Блог'
    ],
    'testimonials' => [
        'heading' => 'Відгуки про магазин',
        'form-heading' => 'Залишити відгук'
    ],
    'contacts' => [
        'heading' => 'Контакти',
        'columns' => [
            'address' => [
                'heading' => 'Адреса'
            ],
            'telephones' => [
                'heading' => 'Телефони'
            ],
            'emails' => [
                'heading' => 'E-mail'
            ],
            'schedule' => [
                'heading' => 'Графік роботи'
            ]
        ],
    ]
];
