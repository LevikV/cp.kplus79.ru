<?php
//
//
set_time_limit(900);
//
ignore_user_abort(true);
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include $_SERVER['DOCUMENT_ROOT'] . '/price/classes/' . $class . '.php'; // Путь для запуска напрямую, при отладке
    //include 'price\classes\\' . $class . '.php';
    //include 'classes\\' . $class . '.php'; // Путь для запуска командой из под винды сервисом через скрипт
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php'); // Путь для запуска напрямую, при отладке
//require_once('price\system\config.php');
//require_once('system\config.php'); // Путь для запуска командой из под винды сервисом через скрипт

// Объявляем глобальный массив ошибок
$ERROR = array();
// Инициализируем объект для доступа к БД  и переменные
$db = new Db;
$vtt = new Vtt;
//
$flag_error = false;
$count_vtt_msk = 0;
$count_vtt_khv = 0;
//
$prov_id_vtt_msk = 1;
$prov_id_vtt_khv = 1;

$argv[1] = 0;
//$argv[2] = 10;

//
if ($argv[1] == 1) {
    // Если воркер запускается поверх зависшего воркера, нужно проверить пул и т.д.

} else {
    // Воркер запускается впервые
    // меняем статус задачи
    $db->editSystemTask('worker_vtt_runtime', 'working');
    // Создаем временную пул-таблицу тоталов по поставщику
    $db->createPullProviderRunTime($prov_id_vtt_msk);
    //
    // Получаем все необходимые данные для работы обновления остатков
    // Получаем ассоциативный массив тоталов поставщика из
    // нашей базы первым ключом которого является id поставщика, а вторым ключом id товара из нашей базы
    $pull_provider_runtime = $db->getPullProviderRunTime();
    if ($pull_provider_runtime === false OR $pull_provider_runtime === null) $flag_error = true;
    // Получаем тоталы с портала ВТТ в виде ассоциативного массива, где ключи id товара, как у поставщика
    $pull_vtt_runtime = $vtt->getProductsTotal();
    // Нужно сформировать таблицу сопоставления наших id товаров поставщика с id товара как у поставщика
    // Получаем ассоциативный массив ключами которого id товаров как у поставщика, а значения id из нашей базы
    $map_our_prod_id_by_prov_prod_id_index = $db->getMapPullProductIdByProviderProductIndex($prov_id_vtt_msk);
    if ($map_our_prod_id_by_prov_prod_id_index === false OR $map_our_prod_id_by_prov_prod_id_index === null) $flag_error = true;
    //
    if ($flag_error) {
        //$db->sendErrorMessageToMail($message);
        exit();
    }
    //
    foreach ($pull_vtt_runtime as $vtt_runtime) {
        if (isset($map_our_prod_id_by_prov_prod_id_index[$vtt_runtime['id']])) {
            // Если в базе есть товар с этим id то его надо обновить
            $data = array();
            $data['provider_id'] = $prov_id_vtt_msk;
            $data['product_id'] = $map_our_prod_id_by_prov_prod_id_index[$vtt_runtime['id']];
            $data['total'] = $vtt_runtime['main_office_quantity'];
            $data['price'] = $vtt_runtime['price'];
            $prov_total_add = $db->edit2ProviderProductTotal($data);
            if ($prov_total_add == false) {
                $message .= 'Ошибка добавления totals' . "\r\n";
                $message .= 'provider_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'product_id: ' . $data['product_id'] . "\r\n";
                $db->addLog('ERROR', 'VTT', $message);
                //$count_error++;
            } else {
                $count_vtt_msk++;
                $db->finishWorkerProviderRunTimeTransact($data['product_id'], $data['provider_id']);
            }
            //
            $data = array();
            $data['provider_id'] = $prov_id_vtt_khv;
            $data['product_id'] = $map_our_prod_id_by_prov_prod_id_index[$vtt_runtime['id']];
            $data['total'] = $vtt_runtime['available_quantity'];
            $data['transit'] = $vtt_runtime['transit_quantity'];
            $data['price'] = $vtt_runtime['price'];
            $prov_total_add = $db->edit2ProviderProductTotal($data);
            if ($prov_total_add == false) {
                $message .= 'Ошибка добавления totals' . "\r\n";
                $message .= 'provider_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'product_id: ' . $data['product_id'] . "\r\n";
                $db->addLog('ERROR', 'VTT', $message);
                //$count_error++;
            } else {
                $count_vtt_khv++;
                $db->finishWorkerProviderRunTimeTransact($data['product_id'], $data['provider_id']);
            }
        }
    }
    echo $count_vtt_khv;
    echo '<br>';
    echo $count_vtt_msk;



//
}

/*
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
*/