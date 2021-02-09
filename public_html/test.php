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
$data = load_alldata();
echo "Количество элементов вернувшихся методом GetItems: ";
echo count($data->GetItemsResult->ItemDto);
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
echo "<br>";
//
for ($i=0; $i < count($categories); $i++) {
    $result = load_datacategory($categories[$i][0]);
    //Преобразование SOAP объекта в массив PHP
    $items = is_array($result->GetCategoryItemsResult->ItemDto)
        ? $result->GetCategoryItemsResult->ItemDto
        : array($result->GetCategoryItemsResult->ItemDto);
    echo "Количество товаров в категории " . $categories[$i][0] . ": " . count($result->GetCategoryItemsResult->ItemDto) . "<br>";
}
echo "<br>";
print_r($categoryall);



/*
for ($i=0; $i < count($categories); $i++) {
    $result = load_datacategory($categories[$i][0]);
    //Преобразование SOAP объекта в массив PHP
    $items = is_array($result->GetCategoryItemsResult->ItemDto)
        ? $result->GetCategoryItemsResult->ItemDto
        : array($result->GetCategoryItemsResult->ItemDto);

    //$result = load_datacategory($categories[$i][0]);
    //echo "Количество товаров в категории " . $categories[$i][0] . ": ";
    //echo count($result->GetCategoryItemsResult->ItemDto);
    }


}
*/




//$result = load_datacategory($categories[4][0]);







//echo '<pre>';
//print_r($iteminfo);
//echo '</pre>';




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