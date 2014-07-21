YandexPddApi
============

Простой и легкий класс для работы с API pdd.yandex.ru, не требующий SimpleXML.

Использование
------

Для работы с API нужно [получить](https://pddimp.yandex.ru/get_token.xml?domain_name=example.com) токен.

```
<?php

require 'YandexPddApi.php';

$mail = new YandexPddApi("token");

```

Регистрация пользователя (принимает две переменные: логин и пароль (опционально))

`$mail->reg_user_token("username");`

Установка пересылки почты

`$mail->set_forward("username", "forward_mail@gmail.com");`

Редактирование пользователя

```
$mail->edit_user("username",
[
	// Новый пароль пользователя
	'password' => 'new_password',
	// Имя
	'iname' => 'John',
	// Фамилия
	'fname' => 'Doe',
	// Пол (0 — не указан / 1 — мужской / 2 — женский)
	'sex'	=> '1',
	// Секретный вопрос
	'hintq' => 'Do you like apples?',
	// Ответ на секретный вопрос
	'hinta' => 'Yes'
]);
```

Удаление пользователя

`$mail->delete_user("username");`

Остальные методы можно найти в [документации](http://api.yandex.ru/pdd/doc/concepts/general.xml).
