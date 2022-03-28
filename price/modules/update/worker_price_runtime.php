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
// Инициализируем объект для доступа к БД  и переменные
$db = new Db;
$flag_error = false;

// Записываем системную задачу-воркера в таблицу системных задач
// чтобы потом была возможность отследить по наличию записи в таблице и времени создания,
// не прервалась ли работа воркера
$db->addSystemTask('worker_price_runtime', 'working', getmypid(), $argv[1], $argv[2]);

// Получаем пул totals переданных воркеру для обработки
$pull_price_runtime_portion = $db->getPullPriceRunTimePortion($argv[1], $argv[2]);
if ($pull_price_runtime_portion === false OR $pull_price_runtime_portion === null) $flag_error = true;
// Получаем все необходимые данные для работы обновления остатков
$pull_provider_runtime = $db->getPullProviderRunTime();
if ($pull_provider_runtime === false OR $pull_provider_runtime === null) $flag_error = true;
$providers_currencies = $db->getPullProvidersCurrencies();
if ($providers_currencies === false OR $providers_currencies === null) $flag_error = true;
$provider_price_groups = $db->getPullProviderPriceGroups();
if ($provider_price_groups === false OR $provider_price_groups === null) $flag_error = true;
$provider_products_price_groups = $db->getPullProviderProductsPriceGroups();
if ($provider_products_price_groups === false OR $provider_products_price_groups === null) $flag_error = true;
$map_products_pull_id_by_prov_index = $db->getMapPullIdByProvIndex('product');
if ($map_products_pull_id_by_prov_index === false OR $map_products_pull_id_by_prov_index === null) $flag_error = true;

// Проверяем все ли данные подготовлены без ошибок и если есть ошибки, прерываем работу воркера
if ($flag_error) {
    //$db->sendErrorMessageToMail($message);
    exit();
}

//
foreach ($pull_price_runtime_portion as $price_runtime_portion) {
    $provider_product_total = $pull_provider_runtime[];
}
