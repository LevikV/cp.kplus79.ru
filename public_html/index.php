<?php
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'price/classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/public_html/price/system/config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();


$my_db = new Db;
$prov_id = 1;
$prov_cat_name = 'Картридж-пленки';
//echo $my_db->getOurCatIdByProvCatId($vtt_cat_id);
print_r($my_db->getOurCatIdByProvCatName($prov_cat_name, $prov_id));


//$vtt = new Vtt;
//$vtt_cat_id = 'CARTBLT';
//$vtt_product_portion = $vtt->getProductPortion(2, 50);
//$vtt_product_portion = $vtt->getProductByCategory($vtt_cat_id);
//print_r($vtt_product_portion);



function loadCategoryVtt () {
    global $ERROR;
    $vtt = new Vtt;
    $vtt->createCategory();
    if (empty($ERROR)) {
        echo 'Загрузка успешно завершена!';
        return true;
    } else {
        foreach ($ERROR as $key => $value) {
            echo 'Error - ' . $key . ': <br>';
            foreach ($value as $item) {
                echo '<br>';
                echo $item;
            }
        }
    }

}



?>