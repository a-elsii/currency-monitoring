<?php

use app\models\My\MyHelper;

$news_array = [
    'id: ' . MyHelper::API_TYPE_INTEGER,
    'title: ' . MyHelper::API_TYPE_STRING,
    'image: ' . MyHelper::API_TYPE_STRING,
    'description: ' . MyHelper::API_TYPE_STRING,
    'dateCreate: ' . MyHelper::API_TYPE_INTEGER,
];

return [
    [
        'method' => MyHelper::API_METHOD_GET,
        'url' => 'all-news',
        'params' => [
            'limit: ' . MyHelper:: API_TYPE_INTEGER,
            'page: ' . MyHelper:: API_TYPE_INTEGER,
        ],
        'return' => $news_array,
        'comment' => 'Получения всех новостей',
    ],
    [
        'method' => MyHelper::API_METHOD_GET,
        'url' => 'news',
        'params' => [
            'id: ' . MyHelper::API_TYPE_INTEGER,
        ],
        'return' => $news_array,
        'comment' => 'Получить новость',
    ],
    [
        'method' => MyHelper::API_METHOD_GET,
        'url' => 'search-news',
        'params' => [
            'text: ' . MyHelper::API_TYPE_STRING,
        ],
        'return' => $news_array,
        'comment' => 'Поиск новостей',
    ],
];
