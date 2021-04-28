<?php
//Глобальные переменные
$wsdl_url = 'http://api.vtt.ru:8048/Portal.svc?singleWsdl'; //ссылка для обращения к API
$login = 'am-072'; // логин
$password = '211212'; // пароль
$f = 0;
$t = 15;
$itemId = '120012111';

//Вывод заголовка
echo "Программа прайса ВТТ";
echo '<br>';

$result = load_alldata();
echo "Количество элементов вернувшихся методом GetItems: ";
echo count($result->GetItemsResult->ItemDto);
echo '<br>';

echo "<pre>";
//print_r($result);
echo "</pre>";
//
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
//
echo "<br>";
echo "Общее количество выгруженных товаров: " . $countitems;
echo "<br>";
//



//Функция загрузки товаров по категориям
function load_datacategory($catid)
{
    global $wsdl_url, $login, $password;
    $params = array("login" => $login , "password" => $password);
    $paramsportion = array("login" => $login , "password" => $password, "categoryId" => $catid);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        $dates = $client->GetCategoryItems($paramsportion);
        return $dates;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка: ".$E->faultstring);
        echo "Error";
        echo '<br>';
        echo $E->faultstring;
        echo '<br>';
        print_r($paramsportion);
        die;
    }
}

//Функция загрузки категорий
function load_categories()
{
    global $wsdl_url, $login, $password;
    $params = array("login" => $login , "password" => $password);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        $categories = $client->GetCategories($params);
        return $categories;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка: ".$E->faultstring);
        echo "Error";
        echo '<br>';
        echo $E->faultstring;
        echo '<br>';
        print_r($paramsportion);
        die;
    }
}

//Функция загрузки всех товаров
function load_alldata()
{
    global $wsdl_url, $login, $password, $itemId, $f, $t;
    $params = array("login" => $login , "password" => $password);
    //$paramsid = array("login" => $login , "password" => $password, "itemId" => $itemId);
    $paramsportion = array("login" => $login , "password" => $password, "from" => $f, "to" => $t);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        //$dates = $client->GetItem($paramsid);
        //$dates = $client->GetItemPortion($paramsportion);
        $dates = $client->GetItems($params);
        return $dates;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка: ".$E->faultstring);
        echo "Error";
        echo '<br>';
        echo $E->faultstring;
        echo '<br>';
        print_r($paramsportion);
        die;
    }
}
//
function load_data()
{
    global $wsdl_url, $login, $password, $itemId, $f, $t;
    $params = array("login" => $login , "password" => $password);
    //$paramsid = array("login" => $login , "password" => $password, "itemId" => $itemId);
    $paramsportion = array("login" => $login , "password" => $password, "from" => $f, "to" => $t);
    try
    {
        $client = new SoapClient($wsdl_url, $params);
        //$dates = $client->GetItem($paramsid);
        $dates = $client->GetItemPortion($paramsportion);
        return $dates;
    }
    catch (SoapFault $E)
    {
        //write_to_log("Ошибка: ".$E->faultstring);
        echo "Error";
        echo '<br>';
        echo $E->faultstring;
        echo '<br>';
        print_r($paramsportion);
        die;
    }
}
?>