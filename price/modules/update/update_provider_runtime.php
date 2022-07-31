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
        }


        $active_worker = false;
        $system_tasks = $db->getSystemTasks();
        foreach ($system_tasks as $system_task) {
            if ($system_task['task'] === 'worker_price_runtime') {
                $delta = time() - strtotime($system_task['date']);
                if (($delta > 900) AND ($delta > 0)) {
                    $del_old_worker = $db->deleteSystemTask($system_task['id']);
                } elseif (($delta < 900) AND ($delta > 0)) {
                    $active_worker = true;
                }
            }
        }
        if ($active_worker) {
            exit();
        } else {
            // Если активного воркера нет, то надо проверить пул и запустить воркеров
            $id_totals_for_update = $db->getPullIdProviderRunTime();
            if ($id_totals_for_update) {
                if (count($id_totals_for_update) > 100) {
                    $threads = 10;
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
                } else {
                    $from_id_total = $id_totals_for_update[0];
                    $to_id_total = $id_totals_for_update[count($id_totals_for_update) - 1];
                    $cmd = 'php -f modules\update\worker_price_runtime.php';
                    $db->execInBackground($cmd, $from_id_total, $to_id_total);
                }
            } elseif ($id_totals_for_update === null) {
                $del_not_actual_price_total = $db->deleteNotActualProductTotal();
                if ($del_not_actual_price_total) {
                    // меняем статус системной задачи на ВЫПОЛНЕНО!
                    $db->editSystemTask('update_price_runtime', 'updated');
                    // Удаляем пулы тоталов для обновлений
                    $db->deletePullProviderRunTimeTable();
                    $db->deletePullPriceRunTimeTable();
                }
            }
        }
    }

}

?>