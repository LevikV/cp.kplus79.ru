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

//$data = load_data();
//$data = load_alldata();
//$categor = $categories[0];
//$items = $data['GetItemsResult']['ItemDto'];
//$category = $categories[1];
//write_to_log("Сведения о товарах получены. Всего товаров ".count($data->GetItemsResult->ItemDto));
echo count($data->GetItemsResult->ItemDto);
echo '<br>';
//echo $categories->GetCategoriesResult->CategoryDto[0]->Id;
echo '<br>';


//
//Получение списка категорий от ВТТ в виде объекта SOAP
$categoriesobj = load_categories();
//Если SOAP объект содержит одно значение, то он возращает массив, если
//более одного значения, то возвращается SOAP объект, который нужно
//преобразовать в массив PHP
$categoryall = is_array($categoriesobj->GetCategoriesResult->CategoryDto)
    ? $categoriesobj->GetCategoriesResult->CategoryDto
    : array($categoriesobj->GetCategoriesResult->CategoryDto);
//Заполнение массива категорий
$i=0;
$categories = array();
foreach ($categoryall as $category) {
    if ($category->ParentId == "") {
        $categories[$i][0] = $category->Id;
        $categories[$i][1] = $category->Name;
        $i++;
        echo $i;
    }
}


//
//Получение списка товаров по категориям, чтобы не нагружать канал
//и сразу запись в файл
$fp = fopen('vtt_price.csv', 'w');
//Записываем строку заголовков
$caption = array("Artikul", "Name", "Manafacture", "Description", "Group0", "Group1", "Group2", "Quantity", "Price", "Width",
    "Height", "Depth", "Weight", "PhotoUrl", "PartNumber", "Vendor", "Compatibility", "ColorName");
fputcsv($fp, $caption, ';', '"');
//А теперь записываем данные о товаре
$iteminfo = array();
//for ($i=0; $i < count($categories); $i++) {
for ($i=0; $i < count($categories); $i++) {

    $result = load_datacategory($categories[$i][0]);
    //Преобразование SOAP объекта в массив PHP
    $items = is_array($result->GetCategoryItemsResult->ItemDto)
        ? $result->GetCategoryItemsResult->ItemDto
        : array($result->GetCategoryItemsResult->ItemDto);
    foreach ($items as $item) {
        $iteminfo['Artikul'] = $item->Id;
        $iteminfo['Name'] = $item->Name;
        $iteminfo['Manafacture'] = $item->Brand;
        $iteminfo['Description'] = $item->Description;
        $iteminfo['Group0'] = "ЗИП для оргтехники";
        $iteminfo['Group1'] = $item->RootGroup;
        $iteminfo['Group2'] = $item->Group;
        $iteminfo['Quantity'] = substr($item->MainOfficeQuantity, 0, 5);
        $iteminfo['Price'] = substr($item->Price, 0, 5);
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


}
fclose($fp);


//$result = load_datacategory($categories[4][0]);







echo '<pre>';
print_r($iteminfo);
echo '</pre>';




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