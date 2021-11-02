<?php
// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'price/classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/public_html/price/system/config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();

//loadCategoryVtt();
//loadManufacturertVtt();
//loadModeltVtt();
//loadVendorVtt();
//loadAttributeVtt();
//loadAllSettingVtt();
//loadProductBaseDataVtt();
//updateProductTotalDataVtt();

//$prov_id = 1;
//$product_id = 86;
//$data['attribute_name'] = 'Цвет';
//$data['attribute_group_name'] = 'Основные';
//$data['attribute_value'] = 'Orange'; // По умолчаниюк цвет Bk для этого товара
//
//
//$my_db = new Db;
//$my_db->editProviderProductAttributeValueByAttribName($prov_id, $product_id, $data);

//echo $my_db->getProviderProductCount(1);
//$my_db->addLog('INFO', 'VTT', 'Test adds log!');

//echo $my_db->getOurProviderProductIdByProviderProductId(1, '99690604444');
//$my_db->checkProviderProductId(1);
//$prov_id = 1;
//echo $my_db->getProviderProductCount($prov_id);
//echo '<br>';
//$date = strtotime('2021-11-08T00:00:00');
//print_r($date);
//echo '<br>';
//$prov_cat_name = 'Картридж-пленки';
//echo $my_db->getOurCatIdByProvCatId($vtt_cat_id);
//print_r($my_db->getOurCatIdByProvCatName($prov_cat_name, $prov_id));


//$vtt = new Vtt;
//$total = $vtt->getTotalProductByProdPortion();
//$total_except = 0;
//foreach (VTT_CATEGORY_ID_EXCEPT as $cat) {
//    $total_except += $vtt->getTotalProductByCategoryId($cat);
//}
//echo $total;
//echo '<br>';
//echo $total_except;

//$vtt_cat_id = 'PARTSCART_ROLMAG';
//$vtt_product_portion = $vtt->getProductPortion(15358, 15369);
//$vtt_product_portion = $vtt->getProductByCategory($vtt_cat_id);
//$vtt_product_total = $vtt->getTotalProductByCategoryId($vtt_cat_id);
//$vtt_product_total = $vtt->getTotalProductByProdPortion();
//print_r($vtt_product_portion);
//echo '<br>';


//$main_cat = $vtt->getMainCategories();
//$main_cat_array  = array();
//foreach ($main_cat as $cat) {
//    $main_cat_array[] = $cat['id'];
//}
//$all_categories = $vtt->getAllCategories();
//$total_cat = 0;
//foreach ($all_categories as $category) {
//    if (in_array($category['parent_id'], $main_cat_array)) {
//        $total  = $total + (int)$vtt->getTotalProductByCategoryId($category['id']);
//        echo 'В категории ' . $category['id'] . ' содержится позиций: '. $vtt->getTotalProductByCategoryId($category['id']);
//        echo '<br>';
//    }
//}
//echo '<br>';
//echo 'Всего по категориям позиций: ' . $total;


function loadCategoryVtt () {
    global $ERROR;
    $vtt = new Vtt;
    $vtt->createCategory();
    if (empty($ERROR)) {
        echo 'Загрузка категорий успешно завершена!';
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

function loadManufacturertVtt () {
    // Функция загрузки и создания производителей в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    if ($products == false) {
        echo 'Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров для выборки опций с количеством
        // товаров отдаваемых запросами о количестве с ВТТ
        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку производителей
            $manufacturer = $vtt->createManufacturer($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }

        if ($manufacturer) {
            echo 'Загрузка выполнена успешно!';
        }
    }
    if (empty($ERROR)) {
        echo 'При загрузка производителей не было ошибок!';
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

function loadModeltVtt () {
    // Функция загрузки и создания моделей в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    if ($products == false) {
        echo '<br>Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров для выборки опций с количеством
        // товаров отдаваемых запросами о количестве с ВТТ
        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку моделей
            $model = $vtt->createModel($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }
        if ($model) {
            echo '<br>Загрузка моделей выполнена успешно!';
        }
    }
    if (empty($ERROR)) {
        echo '<br>При загрузка моделей не было ошибок!';
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

function loadVendorVtt () {
    // Функция загрузки и создания Вендоров в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    if ($products == false) {
        echo '<br>Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров для выборки опций с количеством
        // товаров отдаваемых запросами о количестве с ВТТ
        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку вендоров
            $vendor = $vtt->createVendor($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }

        if ($vendor) {
            echo '<br>Загрузка Вендоров выполнена успешно!';
        }
    }
    if (empty($ERROR)) {
        echo '<br>При загрузка Вендоров не было ошибок!';
        return true;
    } else {
        echo '<br>';
        foreach ($ERROR as $key => $value) {
            echo 'Error - ' . $key . ': <br>';
            foreach ($value as $item) {
                echo '<br>';
                echo $item;
            }
        }
    }

}

function loadAttributeVtt () {
    // Функция загрузки и создания аттрибутов в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    //$products = $vtt->getProductPortion(20, 1000);
    //$products = $products['products'];
    if ($products == false) {
        echo '<br>Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров для выборки опций с количеством
        // товаров отдаваемых запросами о количестве с ВТТ
        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку аттрибутов
            $attribute = $vtt->createAttribute($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }

        if ($attribute) {
            echo '<br>Загрузка аттрибутов выполнена успешно!';
        }
    }
    if (empty($ERROR)) {
        echo '<br>При загрузка аттрибутов не было ошибок!';
        return true;
    } else {
        echo '<br>';
        foreach ($ERROR as $key => $value) {
            echo 'Error - ' . $key . ': <br>';
            foreach ($value as $item) {
                echo '<br>';
                echo $item;
            }
        }
    }

}

function loadAllSettingVtt () {
    // Функция загрузки и создания Производителей, моделей, Вендоров, аттрибутов в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    //$products = $vtt->getProductPortion(20, 1000);
    //$products = $products['products'];
    if ($products == false) {
        echo '<br>Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров для выборки опций с количеством
        // товаров отдаваемых запросами о количестве с ВТТ
        echo '<br>Получено товаров: ' . count($products);
        echo '<br>';
        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку производителей
            $manufacturer = $vtt->createManufacturer($products);
            // Производим загрузку моделей
            $models = $vtt->createModel($products);
            // Производим загрузку вендоров
            $vendors = $vtt->createVendor($products);
            // Производим загрузку аттрибутов
            $attribute = $vtt->createAttribute($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }

        if ($manufacturer) {
            echo '<br>Загрузка аттрибутов выполнена успешно!';
        } else {
            echo '<br>При загрузке аттрибутов произошли ошибки!';
        }
        if ($models) {
            echo '<br>Загрузка моделей выполнена успешно!';
        } else {
            echo '<br>При загрузке моделей произошли ошибки!';
        }
        if ($vendors) {
            echo '<br>Загрузка вендоров выполнена успешно!';
        } else {
            echo '<br>При загрузке вендоров произошли ошибки!';
        }
        if ($attribute) {
            echo '<br>Загрузка аттрибутов выполнена успешно!';
        } else {
            echo '<br>При загрузке аттрибутов произошли ошибки!';
        }
    }
    if (empty($ERROR)) {
        echo '<br>При загрузка данных не было ошибок!';
        return true;
    } else {
        echo '<br>';
        foreach ($ERROR as $key => $value) {
            echo 'Error - ' . $key . ': <br>';
            foreach ($value as $item) {
                echo '<br>';
                echo $item;
            }
        }
    }

}

function loadProductBaseDataVtt () {
    // Функция загрузки и создания продуктов в Пустой БД
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $products = $vtt->getAllProductByCategory();
    if ($products == false) {
        echo '<br>Не удалось получить все товары с портала ВТТ.';
        return false;
    } else {
        // Сравниваем количество полученных товаров с количеством
        // товаров отдаваемых запросами о количестве с ВТТ

        echo 'Количество всех товаров на портале ВТТ: ' . $vtt->getTotalProductByProdPortion() . '<br>';
        echo 'Количество полученных продуктов (учитывая категории-исключения) для загрузки с портала ВТТ: ' . count($products) . '<br>';
        foreach (VTT_CATEGORY_ID_EXCEPT as $cat) {
            echo 'Количество товаров в категории исключении ' . $cat . ' равно: ' . $vtt->getTotalProductByCategoryId($cat) . '<br>';
        }


        if ($vtt->checkTotalProductByVtt(count($products))) {
            // Производим загрузку товаров и основных данных
            $product = $vtt->createProduct($products);
        } else {
            echo '<br>Количество полученных товаров не сходится с количеством товаров отдваваемым запросом с ВТТ';
            return false;
        }

        if ($product) {
            echo '<br>Загрузка товаров завершена';
        } else {
            echo '<br>Загрузку товаров выполнить не удалось';
        }
    }
    if (empty($ERROR)) {
        echo '<br>При загрузка продуктов не было ошибок!';
        return true;
    } else {
        echo '<br>';
        foreach ($ERROR as $key => $value) {
            echo 'Error - ' . $key . ': <br>';
            foreach ($value as $item) {
                echo '<br>';
                echo $item;
            }
        }
    }

}

function updateProductTotalDataVtt () {
    // Функция загрузки, создания и обновления total ВТТ
    global $ERROR;
    $vtt = new Vtt;
    // Получаем все продукты с портала методом Категорий
    $totals = $vtt->updateProductsTotal();
    if ($totals == false) {
        echo '<br>Не удалось обновить total для товаров с портала ВТТ.';
        return false;
    } else {
        echo '<br>Загрузка total товаров выполнена';
    }

    if (empty($ERROR)) {
        echo '<br>При загрузка total не было ошибок!';
        return true;
    } else {
        echo '<br>';
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