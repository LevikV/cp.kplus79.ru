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

    } elseif ($_GET['operation'] == 'update_vtt_products_runtimes') {
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
        $prov_attrib_values = $db->getProviderAttributeValues(1, 4);
        foreach ($prov_attrib_values as $prov_attrib_value) {
            $id = $prov_attrib_value['id'];
            $prod_vtt_life_time = $prov_attrib_value['value'];

            $prod_vtt_life_time = rtrim($prod_vtt_life_time, 'K');
            $prod_vtt_life_time = str_replace(',','.', $prod_vtt_life_time);
            $prod_vtt_life_time = (float)$prod_vtt_life_time * 1000;

            $attrib_value_edit = $db->editProviderAttributeValue($id, (int)$prod_vtt_life_time);

        }
    }
}