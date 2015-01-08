# YandexPddApi
Lightweight and simple pdd.yandex.ru API wrapper.

Использование
------
Для работы работы с API нужно [получить](https://pddimp.yandex.ru/api2/admin/get_token) токен.

```
{
    "require": {
        "somepony/yandexpddapi": "dev-master"
    }
}
```

```
<?php

require_once 'vendor/autoload.php';

$api = new \Somepony\YandexPddApi\API('token');
```

Регистрация нового почтового ящика<br />
`$api->email()->add(['domain' => 'example.com', 'username' => 'John_Doe', 'password' => 'strongpassword']);`

Редактирование существующего почтового ящика<br />
```
$api->email()->edit([
    'domain' => 'example.com',
    'login' => 'John_Doe',
    # Задаем новый пароль
    'password' => 'nowpasswordisevenmorestronger',
    # Имя
    'iname' => 'John',
    # Фамилия
    'fname' => 'Doe',
    # Секретный вопрос
    'hintq' => 'Do you like apples?',
    # Ответ на секретный вопрос
    'hinta' => 'Yes',
]);
```

Удаление почтового ящика<br />
`$api->email()->del(['domain' => 'example.com', 'login' => 'John_Doe']);`
