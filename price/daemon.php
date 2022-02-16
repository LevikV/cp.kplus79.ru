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

    } elseif ($_GET['operation'] == 'temp') {
        $db = new Db;
        print_r($db->getMapByProvItemId('manufacturer', 22));
    }
}