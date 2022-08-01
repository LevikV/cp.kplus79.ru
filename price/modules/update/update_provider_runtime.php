<?php
// Скрипт обновления totals поставщиков
// работает как процесс в фоновом режиме
// При первом запуске должен создать таблицу-пул id для обновления и изменить статус системной задачи.

//
ignore_user_abort(true);
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    //include $_SERVER['DOCUMENT_ROOT'] . '/price/classes/' . $class . '.php';
    //include 'price\classes\\' . $class . '.php';
    include 'classes\\' . $class . '.php'; // Путь когда скрипт вызывается командой как сервис в Windows
});
// Загружаем глобальные настройки
//require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
//require_once('price\system\config.php');
require_once('system\config.php'); // Путь когда скрипт вызывается командой как сервис в Windows
// Объявляем глобальный массив ошибок
$ERROR = array();

//

$db = new Db;
//
//if (isset($argv[1])) {
//    $db->editSystemTask('update_provider_runtime', $argv[1]);
//}
//
$update_provider_runtime = $db->getSystemTask('update_provider_runtime');
if ($update_provider_runtime) {
    // Смотрим статус задания, чтобы определить первый это запуск скрипта или нет
    if ($update_provider_runtime['status'] == 'updated') {
        // запуск первый, надо запустить воркеров обновления по поставщикам
        // меняем статус задачи обновления на В работе
        $db->editSystemTask('update_provider_runtime', 'working');
        // Воркер обновления ВТТ
        $cmd = 'php -f modules\update\worker_vtt_runtime.php';
        $db->execInBackground($cmd, 0, 0);
        sleep(30);
        // Воркер обновления Булат
        $cmd = 'php -f modules\update\worker_bulat_runtime.php';
        $db->execInBackground($cmd, 0, 0);
    } elseif ($update_provider_runtime['status'] == 'working') {
        // Обновление прайса в работе
        // Проверяем есть ли активный воркер
        $worker_vtt_runtime = $db->getSystemTask('worker_vtt_runtime');
        $worker_bulat_runtime = $db->getSystemTask('worker_bulat_runtime');
        if ($worker_vtt_runtime['status'] == 'working') {
            $delta = time() - strtotime($worker_vtt_runtime['date']);
            if ($delta > 1800) {
                // Если время выполнения задачи больше 30 минут, то надо запустить Воркер обновления ВТТ
                //с параметром 1, чтобы воркер проверил пул и завершил задачу
                $cmd = 'php -f modules\update\worker_vtt_runtime.php';
                $db->execInBackground($cmd, 1, 0);
            }
        } elseif ($worker_vtt_runtime['status'] == 'updated') {
            $delta_vtt = $update_provider_runtime['date'] - $worker_vtt_runtime['date'];
            if ($delta_vtt > 0) {
                // Если по каким то причинам Воркер обновления ВТТ не запустился сразу, пробуем запустить еще раз
                $cmd = 'php -f modules\update\worker_vtt_runtime.php';
                $db->execInBackground($cmd, 0, 0);
            }
        }
        //
        if ($worker_bulat_runtime['status'] == 'working') {
            $delta = time() - strtotime($worker_bulat_runtime['date']);
            if ($delta > 1800) {
                // Если время выполнения задачи больше 30 минут, то надо запустить Воркер обновления ВТТ
                //с параметром 1, чтобы воркер проверил пул и завершил задачу
                $cmd = 'php -f modules\update\worker_bulat_runtime.php';
                $db->execInBackground($cmd, 1, 0);
            }
        } elseif ($worker_bulat_runtime['status'] == 'updated') {
            $delta_bulat = $update_provider_runtime['date'] - $worker_bulat_runtime['date'];
            if ($delta_bulat > 0) {
                // Если по каким то причинам Воркер обновления Булат не запустился сразу, пробуем запустить еще раз
                $cmd = 'php -f modules\update\worker_bulat_runtime.php';
                $db->execInBackground($cmd, 0, 0);
            }
        }
        //
        // Проверяем, выполнены ли обновления по поставщикам и если выполнены
        // меняем статус обновления тоталов по поставщикам в обновлен
        if (isset($delta_vtt) AND isset($delta_bulat)) {
            if (($delta_vtt < 0) AND ($delta_bulat < 0)) {
                $db->editSystemTask('update_provider_runtime', 'updated');
            }
        }
    }

}

?>