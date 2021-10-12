<?php
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'price/classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/public_html/price/system/config.php');

loadPriceVtt();

function loadPriceVtt () {
    $vtt = new Vtt;
    $vtt_main_category = $vtt->getMainCategories();
    echo '<pre>';
    print_r($vtt_main_category);
    echo '</pre>';

}


function load_alldata()
{
    global $wsdl_url, $login, $password;
    $params = array("login" => $login , "password" => $password);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        $dates = $client->GetItems($params);
        //write_to_log("Данные с портала ВТТ успешно получены.");
        return $dates;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка получения данных с портала ВТТ: ".$E->faultstring);
        $subject = "Интернет магазин Картридж+ - ОШИБКА получения прайса с ВТТ";
        $message = "Произошла ошибка при получении данных с портала ВТТ \r\n";
        $message = $message . "Ошибка: " . $E->faultstring;
        //send_mail($subject, $message);
        die;
    }
}

function getCategoriesVtt () {
    global $wsdl_url, $login, $password;
    $params = array("login" => $login , "password" => $password);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        $dates = $client->GetCategories($params);
        //write_to_log("Данные с портала ВТТ успешно получены.");
        return $dates;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка получения данных с портала ВТТ: ".$E->faultstring);
        $subject = "Интернет магазин Картридж+ - ОШИБКА получения прайса с ВТТ";
        $message = "Произошла ошибка при получении данных с портала ВТТ \r\n";
        $message = $message . "Ошибка: " . $E->faultstring;
        //send_mail($subject, $message);
        die;
    }
}



?>