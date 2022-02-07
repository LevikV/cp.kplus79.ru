<?php
// Данный модуль нужен для выполнения фоновых операций по работе с БД
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
if (isset($_POST['operation'])) {
    // Действие добавления новой модели в эталонную базу
    // возвращает результат в JSON формате, в результате id добавленной модели и id карты сопоставления
    if ($_POST['operation'] == 'add_model_from_prov') {
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['model_name'];
        // добавляем модель
        $model_id = $db->addModel($data);
        if ($model_id) {
            $json['model_id'] = $model_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('model', $model_id, $_POST['prov_model_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты модели';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении модели в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_manuf_from_prov') {
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['manuf_name'];
        // добавляем производителя
        $manuf_id = $db->addManufacturer($data);
        if ($manuf_id) {
            $json['manuf_id'] = $manuf_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('manufacturer', $manuf_id, $_POST['prov_manuf_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты производителя';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении производителя в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_vendor_from_prov') {
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['vendor_name'];
        // добавляем вендора
        $vendor_id = $db->addVendor($data);
        if ($vendor_id) {
            $json['vendor_id'] = $vendor_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('vendor', $vendor_id, $_POST['prov_vendor_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты вендора';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении вендора в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_attrib_group_from_prov_all') {
        $db = new Db;
        // формируем данные
        $attrib_groups_to_add = array();
        $attrib_groups_to_add = $_POST['attrib_groups_to_add'];

        $providers = array();
        foreach ($attrib_groups_to_add as $attrib_group) {
            if (!in_array($attrib_group['provider_id'], $providers, true)) {
                $providers[] = $attrib_group['provider_id'];
            }
        }




        echo json_encode($data);
    }
}
