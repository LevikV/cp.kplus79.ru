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
if (isset($_GET['operation'])) {
    // Действие добавления новой модели в эталонную базу
    if ($_GET['operation'] == 'add_model_from_prov') {
        $price = new Price;
        $data = array();
        $data['name'] = $_GET['model_name'];
        $data['prov_model_id'] = $_GET['prov_model_id'];
        $result = $price->addModelFromProv($data);



        $model_name = $_GET['model_name'];
        $prov_model_id = $_GET['prov_model_id'];
        echo $model_name . $prov_model_id;
    }
}
