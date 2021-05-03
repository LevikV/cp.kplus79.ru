<?php
//Глобальные переменные
$wsdl_url = 'http://api.vtt.ru:8048/Portal.svc?singleWsdl'; //ссылка для обращения к API
$login = 'am-072'; // логин
$password = '211212'; // пароль

//Загружаем все данные с сервера поставщика
$result = load_alldata();
//Записываем полученные данные в файла
whrite_price($result);
//Получаем курс доллара
$kurs = get_kurs();
//Устанавливаем цену по курсу и делаем разную наценку на ориги и совместимку
process_price($kurs);
//

//
//Функция загрузки всех товаров с портала ВТТ
function load_alldata()
{
    global $wsdl_url, $login, $password;
    $params = array("login" => $login , "password" => $password);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        $dates = $client->GetItems($params);
        write_to_log("Данные с портала ВТТ успешно получены.");
        return $dates;
    }
    catch (SoapFault $E)
    {
        write_to_log("Ошибка получения данных с портала ВТТ: ".$E->faultstring);
        $subject = "Интернет магазин Картридж+ - ОШИБКА получения прайса с ВТТ";
        $message = "Произошла ошибка при получении данных с портала ВТТ \r\n";
        $message = $message . "Ошибка: " . $E->faultstring;
        send_mail($subject, $message);
        die;
    }
}
//Функция записи полученных данных в файл в формате CSV
function whrite_price($result) {
    $fp = fopen('vtt_price_all_new.csv', 'w');
//Записываем строку заголовков
    $caption = array("Artikul", "Name", "Manafacture", "Description", "Group0", "Group1", "Group2", "Quantity", "Price", "Width",
        "Height", "Depth", "Weight", "PhotoUrl", "PartNumber", "Vendor", "Compatibility", "ColorName");
    fputcsv($fp, $caption, ';', '"');
//А теперь получаем и записываем данные о товаре в файл
    $iteminfo = array();
    $countitems = 0;
//
//Преобразование SOAP объекта в массив PHP
    $items = is_array($result->GetItemsResult->ItemDto)
        ? $result->GetItemsResult->ItemDto
        : array($result->GetItemsResult->ItemDto);
    foreach ($items as $item) {
        $countitems++;
        $iteminfo['Artikul'] = $item->Id;
        $iteminfo['Name'] = $item->Name;
        $iteminfo['Manafacture'] = $item->Brand;
        $iteminfo['Description'] = $item->Description;
        $iteminfo['Group0'] = "ЗИП для оргтехники";
        $iteminfo['Group1'] = $item->RootGroup;
        //Исключаем из выборки ненужные категории
        if ($iteminfo['Group1'] == 'Маркетинговые материалы'){
            $countitems--;
            continue;
        }
        if ($iteminfo['Group1'] == 'Компьютер. запчасти и аксессуары') {
            $countitems--;
            continue;
        }
        //Идем дальше
        $iteminfo['Group2'] = $item->Group;
        $iteminfo['Quantity'] = substr($item->MainOfficeQuantity, 0, 5);
        //Преобразовываем цену, если отрицательная (т.е. по запросу), то ставим 0
        $price = substr($item->Price, 0, 5);
        if ($price<0) {$price = 0;}
        $iteminfo['Price'] = $price;
        $iteminfo['Width'] = substr($item->Width, 0, 5);
        $iteminfo['Height'] = substr($item->Height, 0, 5);
        $iteminfo['Depth'] = substr($item->Depth, 0, 5);
        $iteminfo['Weight'] = substr($item->Weight, 0, 5);
        $iteminfo['PhotoUrl'] = $item->PhotoUrl;
        $iteminfo['PartNumber'] = $item->OriginalNumber;
        $iteminfo['Vendor'] = $item->Vendor;
        $iteminfo['Compatibility'] = str_replace(array("\r\n", "\r", "\n"), '', $item->Compatibility);
        $iteminfo['ColorName'] = $item->ColorName;
        fputcsv($fp, $iteminfo, ';', '"');
    }
    fclose($fp);
    write_to_log("Запись первичных данных завершена успешно. Итого: " . $countitems);
}
//Функция получения курса доллара с сайта центробанка РФ
function get_kurs() {
    $url = "https://www.cbr.ru/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//для возврата результата в виде строки, вместо прямого вывода в браузер
    $returned = curl_exec($ch);
    curl_close ($ch);
//
    if ($returned == false) { //Если произошла ошибка при получении курса, то возвращаем курс по умолчанию и пишем лог
        $kurs = 77;
        write_to_log("Ошибка получения курса доллара");
        $subject = "Интернет магазин Картридж+ - ОШИБКА получения курса доллара";
        $message = "Произошла ошибка при получении данных курса доллара с сайта ЦРБ РФ. \r\n";
        $message = $message . "Установлен курс по умолчанию.\r\n";
        send_mail($subject, $message);
    } else {
        $temp = substr($returned, strpos($returned, "_dollar"), 100);
        $temp = substr($temp,75,7);
        $temp[2] = '.';
        $kurs = ceil($temp);
        write_to_log("Данные курса успешно получены: " . $kurs);
    }
    return $kurs;
}
//Функция обработки прайс листа
function process_price($kurs) {
    $artikulOld = array();
    $fOld = fopen('vtt_price_im.csv', 'r');
    $i = 0;
    while (($data = fgetcsv($fOld, 0, ';', '"')) !== FALSE) {
        if ($i == 1) $artikulOld[] = $data[0];
        $i = 1;
    }
    fclose($fOld);
//
    $fVtt = fopen('vtt_price_all_new.csv','r');
    $fIm = fopen('vtt_price_im.csv', 'w');
//Записываем строку заголовков
    $caption = array("Artikul", "Name", "Manafacture", "Description", "Group0", "Group1", "Group2", "Quantity", "Price", "Width",
        "Height", "Depth", "Weight", "PhotoUrl", "PartNumber", "Vendor", "Compatibility", "ColorName");
    fputcsv($fIm, $caption, ';', '"');
    $i = 0;
    $t = 0;
    while (($data = fgetcsv($fVtt, 0, ';', '"')) !== FALSE) {
        if ($i>0) {
            //Накрутка цены, в зависимости оригинал или нет
            if ((stripos($data[1], '(o)')===false) AND (stripos($data[1], '( o )')===false) AND (stripos($data[1], '(О)')===false) AND (stripos($data[1], '( о )')===false)) {
                $price = $data[8];
                $price = $price*$kurs+$price*$kurs*0.4;
                $price = ceil($price);
                if ($price === 0) $price = '';
                $data[8] = $price;
            } else {
                $price = $data[8];
                $price = $price*$kurs+$price*$kurs*0.1;
                $price = ceil($price);
                if ($price === 0) $price = '';
                $data[8] = $price;
            }
            //
            //Записываем прайс лист для ИМ
            fputcsv($fIm, $data, ';', '"');
            //Проверяем новый это товар или уже есть в базе, по артиклу
            if (!in_array ($data[0], $artikulOld)) {
                if ($t == 0) {
                    $fNew = fopen('log/newGoods' . date("mdY") . '.log', 'w');
                    fputs($fNew, "В базу были добавлены новые товары: \r\n");
                    $message = "В базу были добавлены новые товары: \r\n";
                    $subject = "Интернет магазин Картридж+ - НОВЫЕ ПОЗИЦИИ";
                    $t = 1;
                }
                fputs($fNew, $data[0] . $data[1] . "\r\n");
                $message = $message . $data[0] . " - " . $data[1] . "\r\n";
            }
        }
        $i++;
    }
    fclose($fIm);
    fclose($fVtt);
    if ($t == 1) {
        fclose($fNew);
        write_to_log("В базу добавлены новые позиции.");
        send_mail($subject, $message);
    }
    write_to_log("Формирование прайса успешно завершено. Итого позиций: " . $i);
}
//Функция отправки электронной почты для уведомления
function send_mail($subject, $message) {
    $to      = "<alex@kplus79.ru>, ";
    $to      .= "<dima.zhirov@kplus79.ru>";
    //$subject = 'the subject';
    //$message = "hello\r\n";
    //$message .= "test\r\n";
    $headers = 'From: noreply@kplus79.ru' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
}
//Функция записи данных об ошибках и работе скрипта в лог файл
function write_to_log($logdata) {
    if (file_exists('log/logs.log')) {
        $f = fopen('log/logs.log', a);
        fputs($f, date("mdY: ") . $logdata . "\r\n");
        fclose($f);
    } else {
        $f = fopen('log/logs.log', w);
        fputs($f, date("mdY: ") . $logdata . "\r\n");
        fclose($f);
    }
}

?>