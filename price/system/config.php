<?php
// Параметры подключения к БД
define('DB_SERVER', 'localhost');
define('DB_NAME', 'kp79db_price');
define('DB_USER', 'root');
define('DB_PSWD', 'root');

// Параметры подключения к порталу ВТТ
define('VTT_WSDL_URL', 'http://api.vtt.ru:8048/Portal.svc?singleWsdl');
define('VTT_LOGIN', 'am-072');
define('VTT_PASSWORD', '211212');
// Категории исключения для загрузки
define('VTT_CATEGORY_ID_EXCEPT', array(
    'MARKETING',
    'PARTSPRINT_APPLE',
    'PARTSPC'
));

// Параметры наценки для формирования цен
define('PRICE_COMPATIBLE', 30);
define('PRICE_ORIGINAL', 10);
define('KURS_DOLLAR_RUB', 140);

?>