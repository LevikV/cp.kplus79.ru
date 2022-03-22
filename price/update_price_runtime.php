<?php
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();

$code = $argv[1];
echo $code;
$db = new Db;
$db->editSystemTask('update_price_runtime', 'working');

?>