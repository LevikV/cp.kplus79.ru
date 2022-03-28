<?php
//
//
ignore_user_abort(true);
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    //include $_SERVER['DOCUMENT_ROOT'] . '/price/classes/' . $class . '.php';
    //include 'price\classes\\' . $class . '.php';
    include 'classes\\' . $class . '.php';
});
// Загружаем глобальные настройки
//require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
//require_once('price\system\config.php');
require_once('system\config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();

//

$db = new Db;
$db->addSystemTask('worker_price_runtime', 'working', getmypid(), $argv[1], $argv[2]);
//$pull_price_runtime_portion = $db->getPullPriceRunTimePortion($argv[1], $argv[2]);


