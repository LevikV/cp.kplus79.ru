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
        $price = new Price;
        $price->updateProducts();
    } elseif ($_GET['operation'] == 'update_vtt_products') {
        global $ERROR;
        $vtt = new Vtt;
        $updates_vtt = $vtt->updateProducts();
        if ($updates_vtt == false) {
            echo '<br>Не удалось обновить товары с портала ВТТ.';
        } else {
            echo '<br>Обновление товаров завершено.';
        }

    } elseif ($_GET['operation'] == 'update_runtime') {
        global $ERROR;
        $db = new Db;
        $update_price_runtime = $db->getSystemTask('update_price_runtime');
        $update_provider_runtime = $db->getSystemTask('update_provider_runtime');
        if ($update_price_runtime AND $update_provider_runtime) {
            switch ($update_price_runtime['status']) {
                case 'working':
                    // Если статус в работе, то необходимо запустить мастер процесс обновления runTime эталонной базы
                    // для завершения работы надо обновлением
                    $cmd = 'php -f modules\update\update_price_runtime.php';
                    $db->execInBackground($cmd);
                    echo 'ThinkDo working';
                    break;
                case 'updated':
                    //
                    if ($update_provider_runtime['status'] == 'updated') {
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
        $temp = '2022-03-22 15:51:52';
        $date_now = time();
        echo strtotime($temp);
        echo '<br>';
        echo date('d-m-Y H:i:s');

    }
}