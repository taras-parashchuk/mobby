<?php

return [
    'comparelist' => [
        'heading' => 'Сравнение товаров',
        'button' => [
            'all_attr' => 'Все характеристики',
            'different_attr' => 'Только разные',
            'same_attr' => 'Только одинаковые'
        ],
        'none_category' => 'Без рубрики'
    ],
    'search' => [
        'heading' => [
            'single' => 'Поиск',
            'with_results' => 'Результаты по запросу: :search_phrase',
        ],
        'filter' => [
            'all_categories' => 'Поиск во всех подкатегориях',
            'include_descriptions' => 'Искать в описании'
        ]
    ],
    'checkout' => [
        'heading' => 'Оформление заказа',
        'sections' => [
            'contact' => [
                'heading' => 'Контактные данные',
                'has_account' => 'Уже регистрировались?',
                'login' => 'Войти',
                'full_name' => 'ФИО',
                'telephone' => 'Телефон',
                'email' => 'Email',
                'fast_order' => 'Быстрый заказ',
                'fast_order_help'=> 'Заказ оформляется сразу, все детали уточнит менеджер',
                'errors' => [
                    'full_name' => 'ФИО должно содержать от 2 до 64 символа',
                    'telephone' => 'Неправильный формат телефона',
                    'email' => 'Некорректный email',
                    'input' => 'Введите корректное значение',
                ]

            ],
            'shipping' => [
                'heading' => 'Доставка',
                'method' => 'Способ доставки',
                'choose_method' => 'Выберите метод из списка',
                'errors' => [
                    'method' => 'Выберите метод из списка',
                    'input' => 'Введите корректное значение',
                ]
            ],
            'payment' => [
                'heading' => 'Оплата',
                'method' => 'Способ оплаты',
                'comment' => 'Комментарий',
                'choose_method' => 'Выберите метод из списка',
                'errors' => [
                    'method' => 'Выберите метод из списка',
                    'input' => 'Введите корректное значение',
                    'select' => 'Выберите значение из списка',
                ],
                'button_confirm' => 'Оформить заказ'
            ]
        ],
        'confirm' => [
            'fast_order' => 'Создан быстрый заказ через корзину',
            'full_order' => 'Создан заказ через корзину',
        ],
        'success' => [
            'heading' => 'Заказ принят',
            'text' => [
                'guest' => '<p>Наш менеджер в ближайшем времени свяжется с вами для подтверждения заказа.</p><p>Спасибо за покупку!</p>',
                'registered' => '<p>Вы можете отслеживать историю Ваших заказов на сранице <a class="text-link" href=":account">Моя учетная запись</a> и нажав на <a class="text-link" href=":order-history">История заказов</a>.</p><p>Наш менеджер в ближайшем времени свяжется с вами для подтверждения заказа.</p><p>Спасибо за покупку!</p>'
            ]
        ]
    ],
    'blog' => [
        'heading' => 'Блог'
    ],
    'testimonials' => [
        'heading' => 'Отзывы о магазине',
        'form-heading'=> 'Оставить отзыв',
    ],
    'contacts' => [
        'heading' => 'Контакты',
        'columns' => [
            'address' => [
                'heading' => 'Адрес'
            ],
            'telephones' => [
                'heading' => 'Телефоны'
            ],
            'emails' => [
                'heading' => 'E-mail'
            ],
            'schedule' => [
                'heading' => 'График работы'
            ]
        ],
    ]
];