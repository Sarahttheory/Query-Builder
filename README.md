## Доронина Анна
# QueryBuilder

QueryBuilder - это класс на PHP, предоставляющий унифицированный интерфейс для работы с различными базами данных. Он позволяет строить и выполнять SQL-запросы в удобном и последовательном формате. Класс поддерживает основные операции CRUD (INSERT, UPDATE, DELETE, SELECT), условия, сортировку и ограничение результатов.

## Подготовительные действия
Перед запуском проекта убедитесь, что выполнены следующие требования:

PHP версии 7.0 или выше
Сервер базы данных MySQL или совместимый

## Установка
Склонируйте репозиторий или загрузите файлы QueryBuilder на ваше локальное устройство.

```php
git clone https://github.com/Sarahttheory/QueryBuilder
```

Создайте новую базу данных для проекта.

Настройте параметры подключения к базе данных, изменив файл config.php. 

Обновите значения с учетом ваших учетных данных для базы данных:

```php
return [ 
'host' => 'localhost',
'database' => 'имя_вашей_базы_данных',
'username' => 'ваше_имя_пользователя',
'password' => 'ваш_пароль',
'port' => 3306,
];
```

Включите файл MyQueryBuilder.php в вашем проекте.

```php
require_once 'MyQueryBuilder.php';
```

## Использование
Создайте экземпляр класса MyQueryBuilder, передав массив с настройками подключения к базе данных в конструктор.

```php
$config = require_once 'config.php';
$db = new MyQueryBuilder($config);
```

Используйте доступные методы для построения вашего запроса. Вот несколько примеров:

```php
// Пример SELECT-запроса
$results = $db->select(['table1.column1', 'table1.column2'])
->from('table1')
->join('table2 as t2', 'table1.id = t2.column1', 'LEFT')
->where('table1.column3', '>', 1)
->orderBy('table1.column1', 'ASC')
->limit(10)
->execute();
```
```php
// Пример INSERT-запроса
$insertResult = $db->insert('table1', ['column1' => 'value1', 'column2' => 'value2'])->execute();
```
```php
// Пример UPDATE-запроса
$updateResult = $db->update('table1', ['column1' => 'new_value'])
->where('column2', '=', 'value2')
->execute();
```
```php
// Пример DELETE-запроса
$deleteResult = $db->delete('table1')
->where('column1', '=', 'value1')
->execute();
```
Обрабатывайте результаты запроса или проверяйте статус выполнения.

```php
if ($results !== null) {
// Обработка результата SELECT-запроса
foreach ($results as $row) {
echo $row['column1'] . ' ' . $row['column2'] . '<br>';
}
}

if ($insertResult === true) {
// Обработка результата INSERT-запроса
echo 'Запись успешно добавлена';
}

if ($updateResult === true) {
// Обработка результата UPDATE-запроса
echo 'Запись успешно обновлена';
}

if ($deleteResult === true) {
// Обработка результата DELETE-запроса
echo 'Запись успешно удалена';
}
```

## Внесение вклада
Усли вы обнаружили ошибку или у вас есть предложения по улучшению, пожалуйста, создайте issue или отправьте pull request.
