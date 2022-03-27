<?php
// Скрипт обновления totals в эталонной базе
// работает как процесс в фоновом режиме
// При первом запуске должен создать таблицу-пул id для обновления и изменить статус системной задачи.

//
ignore_user_abort(true);
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include $_SERVER['DOCUMENT_ROOT'] . '/price/classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
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
        $pull_price_runtime = $db->createPullPriceRuntime();
        if ($pull_price_runtime) {
            // необходимо изменить статус задачи и запустить процессы для выполнения обновления
            $db->editSystemTask('update_price_runtime', 'working');
            //
//            $id_totals_for_update = $db->getPullIdPriceRunTime();
//            if ($id_totals_for_update) {
//                $threads = 4;
//                $count_works_thread = ceil(count($id_totals_for_update) / $threads);
//            }




        }

        //$cmd = 'php -f update_price_runtime.php 4 2';
        //$db->execInBackground($cmd);


    }
}

?>