<?php
// Данный модуль нужен для выполнения автоматических заданий по работе с БД
// принимает в качестве аргумента (operation) значение кода операции

// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();

// Проверяем передан ли код операции для выполнения
if (isset($_GET['operation'])) {
    if ($_GET['operation'] == 'update_products') {
        // ОПИСАНИЕ ОПЕРАЦИИ
        //
        $price = new Price;
        $price->updateProducts();
    } elseif ($_GET['operation'] == 'update_provider_runtime') {
        // Операция обновления остатков (тоталов) поставщиков
        // ОПИСАНИЕ ОПЕРАЦИИ
        //
        global $ERROR;
        $db = new Db;
        $update_price_runtime = $db->getSystemTask('update_price_runtime');
        $update_sc_runtime = $db->getSystemTask('update_sc_runtime');
        $update_provider_runtime = $db->getSystemTask('update_provider_runtime');
        $update_provider_product = $db->getSystemTask('update_provider_product');

        //
        if ($update_price_runtime AND $update_provider_runtime AND $update_sc_runtime AND $update_provider_product) {
            switch ($update_provider_runtime['status']) {
                case 'working':
                    // Если статус в работе, то необходимо запустить мастер процесс обновления runTime поставщиков
                    // для завершения работы надо обновлением
                    $cmd = 'php -f modules\update\update_provider_runtime.php';
                    $db->execInBackground($cmd);
                    echo 'ThinkDo working';
                    break;
                case 'updated':
                    // Производим проверку не занята ли база другими процессами обновления
                    $flag_start = true;
                    if ($update_price_runtime['status'] != 'updated') $flag_start = false;
                    if ($update_sc_runtime['status'] != 'updated') $flag_start = false;
                    if ($update_provider_product['status'] != 'updated') $flag_start = false;
                    if ($flag_start) {
                        $delta = time() - strtotime($update_provider_runtime['date']);
                        if ($delta >= 3600) {
                            // Здесь необходимо запустить внешний скрипт обновления price_runtime
                            //passthru("(php -f price/update_price_runtime.php 4 2 & ) >> /dev/null 2>&1");
                            //passthru("(php -f update_price_runtime.php 4 2 & ) > NULL 2>&1");
                            //passthru("(php -f update_price_runtime.php 4 2) > NULL 2>&1 &");
                            //$cmd = 'php -f ' . $_SERVER['DOCUMENT_ROOT'] . '\price\modules\update\update_price_runtime.php';
                            //$cmd = 'php -f price\modules\update\update_price_runtime.php';
                            $cmd = 'php -f modules\update\update_provider_runtime.php';
                            $db->execInBackground($cmd);
                            echo 'ThinkDo updated';

                        }
                    }
                    break;
                case 'error':
                    //
                    echo 'sdsdsddsd';
                    break;
            }
        }

    } elseif ($_GET['operation'] == 'update_vtt_products') {
        // ОПИСАНИЕ ОПЕРАЦИИ
        //
        global $ERROR;
        $vtt = new Vtt;
        $updates_vtt = $vtt->updateProducts();
        if ($updates_vtt == false) {
            echo '<br>Не удалось обновить товары с портала ВТТ.';
        } else {
            echo '<br>Обновление товаров завершено.';
        }

    } elseif ($_GET['operation'] == 'update_price_runtime') {
        // ОПИСАНИЕ ОПЕРАЦИИ
        //
        global $ERROR;
        $db = new Db;
        $update_price_runtime = $db->getSystemTask('update_price_runtime');
        $update_sc_runtime = $db->getSystemTask('update_sc_runtime');
        $update_provider_runtime = $db->getSystemTask('update_provider_runtime');
        $update_provider_product = $db->getSystemTask('update_provider_product');

        if ($update_price_runtime AND $update_provider_runtime AND $update_sc_runtime AND $update_provider_product) {
            switch ($update_price_runtime['status']) {
                case 'working':
                    // Если статус в работе, то необходимо запустить мастер процесс обновления runTime эталонной базы
                    // для завершения работы надо обновлением
                    $cmd = 'php -f modules\update\update_price_runtime.php';
                    $db->execInBackground($cmd);
                    echo 'ThinkDo working';
                    break;
                case 'updated':
                    // Производим проверку не занята ли база другими процессами обновления
                    $flag_start = true;
                    if ($update_provider_runtime['status'] != 'updated') $flag_start = false;
                    if ($update_sc_runtime['status'] != 'updated') $flag_start = false;
                    if ($update_provider_product['status'] != 'updated') $flag_start = false;
                    if ($flag_start) {
                        $delta = time() - strtotime($update_price_runtime['date']);
                        if ($delta >= 1800) {
                            // Здесь необходимо запустить внешний скрипт обновления price_runtime
                            //passthru("(php -f price/update_price_runtime.php 4 2 & ) >> /dev/null 2>&1");
                            //passthru("(php -f update_price_runtime.php 4 2 & ) > NULL 2>&1");
                            //passthru("(php -f update_price_runtime.php 4 2) > NULL 2>&1 &");
                            //$cmd = 'php -f ' . $_SERVER['DOCUMENT_ROOT'] . '\price\modules\update\update_price_runtime.php';
                            //$cmd = 'php -f price\modules\update\update_price_runtime.php';
                            $cmd = 'php -f modules\update\update_price_runtime.php';
                            $db->execInBackground($cmd);
                            echo 'ThinkDo updated';

                        }
                    }
                    break;
                case 'error':
                    //
                    echo 'sdsdsddsd';
                    break;
            }

            // Далее нужно проверить обновление runtime totals у поставщиков

        }





    } elseif ($_GET['operation'] == 'temp') {
        $db = new Db;
        //$temp = '2022-03-22 15:51:52';
        //$date_now = time();
        //echo strtotime($temp);
        //echo '<br>';
        //echo date('d-m-Y H:i:s');
        $duplicates = $db->getProvidersProductsDuplicate();
        foreach ($duplicates as $duplicate) {
            $db->deleteProviderProduct($duplicate['id']);
        }
    }
}