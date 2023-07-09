<?php

require_once 'MyQueryBuilder.php';
$config = require_once 'config.php';

$db = new MyQueryBuilder($config);

// Пример SELECT-запроса
$results = $db->select(['table1.column1', 'table1.column2'])
    ->from('table1')
    ->join('table2 as t2', 'table1.id = t2.column1', 'LEFT')
    ->where('table1.column3', '>', 1)
    ->orderBy('table1.column1', 'ASC')
    ->limit(10)
    ->execute();

if ($results !== null) {
    // Обработка результата SELECT-запроса
    foreach ($results as $row) {
        echo $row['column1'] . ' ' . $row['column2'] . '<br>';
    }
}

// Пример INSERT-запроса
$insertResult = $db->insert('table1', ['column1' => 'value1', 'column2' => 'value2'])->execute();

if ($insertResult === true) {
    // Обработка результата INSERT-запроса
    echo 'Запись успешно добавлена';
}

// Пример UPDATE-запроса
$updateResult = $db->update('table1', ['column1' => 'new_value'])
    ->where('column2', '=', 'value2')
    ->execute();

if ($updateResult === true) {
    // Обработка результата UPDATE-запроса
    echo 'Запись успешно обновлена';
}

// Пример DELETE-запроса
$deleteResult = $db->delete('table1')
    ->where('column1', '=', 'value1')
    ->execute();

if ($deleteResult === true) {
    // Обработка результата DELETE-запроса
    echo 'Запись успешно удалена';
}

