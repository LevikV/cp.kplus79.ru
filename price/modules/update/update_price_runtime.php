<?php
// Скрипт обновления totals в эталонной базе
// работает как процесс в фоновом режиме
// При первом запуске должен создать таблицу-пул id для обновления и изменить статус системной задачи.

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
//
//if (isset($argv[1])) {
//    $db->editSystemTask('update_provider_runtime', $argv[1]);
//}
//
$update_price_runtime = $db->getSystemTask('update_price_runtime');
if ($update_price_runtime) {
    // Смотрим статус задания, чтобы определить первый это запуск скрипта или нет
    if ($update_price_runtime['status'] == 'updated') {
        // запуск первый, надо сгенерировать пул
        $pull_price_runtime = $db->createPullPriceRunTime();
        $pull_provider_runtime = $db->createPullProviderRunTime();
        if ($pull_price_runtime AND $pull_provider_runtime) {
            // необходимо изменить статус задачи и запустить процессы для выполнения обновления
            $db->editSystemTask('update_price_runtime', 'working');
            //
            $id_totals_for_update = $db->getPullIdProviderRunTime();
            if ($id_totals_for_update) {
                $threads = 4;
                $count_works_thread = ceil(count($id_totals_for_update) / $threads);
                for ($i = 0; $i < $threads; $i++) {
                    $from = $i * $count_works_thread;
                    $to = $from + $count_works_thread - 1;
                    $from_id_total = $id_totals_for_update[$from];
                    if ($i === $threads - 1) {
                        while (!isset($id_totals_for_update[$to])) {
                            $to--;
                        }
                        $to_id_total = $id_totals_for_update[$to];
                    } else {
                        $to_id_total = $id_totals_for_update[$to];
                    }
                    //
                    $cmd = 'php -f modules\update\worker_price_runtime.php';
                    $db->execInBackground($cmd, $from_id_total, $to_id_total);
                }


            }




        }

        //$cmd = 'php -f update_price_runtime.php 4 2';
        //$db->execInBackground($cmd);


    }
}

?>