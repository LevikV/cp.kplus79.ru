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
$pull_provider_runtime_portion = $db->getPullProviderRunTimePortion($argv[1], $argv[2]);
if ($pull_provider_runtime_portion === false OR $pull_provider_runtime_portion === null) $flag_error = true;
// Получаем все необходимые данные для работы обновления остатков
//$pull_provider_runtime = $db->getPullProviderRunTime();
//if ($pull_provider_runtime === false OR $pull_provider_runtime === null) $flag_error = true;
//
$providers_currencies = $db->getPullProvidersCurrencies();
if ($providers_currencies === false OR $providers_currencies === null) $flag_error = true;
//
$provider_price_groups = $db->getPullProviderPriceGroups();
if ($provider_price_groups === false OR $provider_price_groups === null) $flag_error = true;
//
$provider_products_price_groups = $db->getPullProviderProductsPriceGroups();
if ($provider_products_price_groups === false OR $provider_products_price_groups === null) $flag_error = true;
//
$map_products_pull_id_by_prov_index = $db->getMapPullIdByProvIndex('product');
if ($map_products_pull_id_by_prov_index === false OR $map_products_pull_id_by_prov_index === null) $flag_error = true;

// Проверяем все ли данные подготовлены без ошибок и если есть ошибки, прерываем работу воркера
if ($flag_error) {
    //$db->sendErrorMessageToMail($message);
    exit();
}

//
foreach ($pull_provider_runtime_portion as $provider_product_total) {
    // Вычисляем розничную цену для товара
    if ((float)$provider_product_total['price'] < 0) {
        $price = 0;
    } else {
        $price = (float)$provider_product_total['price'] * (float)$providers_currencies[$provider_product_total['provider_id']];
    }
    $price = (((int)$provider_price_groups[$provider_products_price_groups[$provider_product_total['product_id']]] / 100) * $price) + $price;
    $price = round($price);
    $data = array();
    $data['total'] = $provider_product_total['total'];
    $data['price_rub'] = $price;
    $data['transit'] = $provider_product_total['transit'];
    $data['transit_date'] = $provider_product_total['transit_date'];
    // Проверяем есть ли в эталонной базе товар сопоставленный с товаром поставщика
    if (isset($map_products_pull_id_by_prov_index[$provider_product_total['product_id']])) {
        $our_product_id = $map_products_pull_id_by_prov_index[$provider_product_total['product_id']];
    } else {
        continue;
    }
    // Добавляем total в эталонную базу
    $add_product_total_id = $db->addProductTotal($our_product_id, $provider_product_total['provider_id'], $data);
    if ($add_product_total_id) {
        // Если total успешно добавился, то необходимо установить статус 1 для транзакции в pull_price_runtime
        $finish_price_transact = $db->finishWorkerPriceRunTimeTransact($our_product_id, $provider_product_total['provider_id']);
        if ($finish_price_transact) {
            // Если удалось успешно отметить выполнение транзакции в pull_price_runtime, то далее
            // так же надо отметить успешность транзакции в pull_provider_runtime
            $finish_provider_transact = $db->finishWorkerProviderRunTimeTransact($provider_product_total['product_id'], $provider_product_total['provider_id']);
        }
    }

}

// После обработки всех данных воркер должен проверить есть ли еще id в пуле id для обновления
// для этого получаем пул и смотрим
