<?php
class Vtt {
    public $status;
    private $client;

    function __construct() {
        global $ERROR;
        $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
        try
        {
            $this->client = new SoapClient(VTT_WSDL_URL, $params);
            if ($this->client != false) {
                $this->status = true;
            }
        }
        catch (SoapFault $E)
        {
            //write_to_log("Ошибка получения данных с портала ВТТ: ".$E->faultstring);
            $ERROR['VTT'][] = 'Ошибка создания SoapClient подключения к ВТТ';
            $this->status = false;
        }

    }

    public function getTotalProductByCategoryId($cat_id) {
        // Функция получения количества товаров по Id категории с портала ВТТ

        global $ERROR;
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD, "categoryId" => $cat_id);
            try {
                $result = $this->client->GetCategoryItems($params);
            } catch (SoapFault $E) {
                //echo $E->faultstring;
                $ERROR['VTT'][] = 'Ошибка получения всех категорий  для получения количества товаров с портала VTT <br>' . $E->faultstring;
                return false;
            }

            $products = is_array($result->GetCategoryItemsResult->ItemDto)
                ? $result->GetCategoryItemsResult->ItemDto
                : array($result->GetCategoryItemsResult->ItemDto);

            return count($products);
        } else {
            return false;
        }
    }

    public function getTotalProductByProdPortion() {
        // Функция получения количества товаров по Id категории с портала ВТТ

        global $ERROR;
        if ($this->status) {
            $from = 2;
            $to = 4;
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD, "from" => $from, "to" => $to);
            try {
                $result = $this->client->GetItemPortion($params);
            } catch (SoapFault $E) {
                //echo $E->faultstring;
                $ERROR['VTT'][] = 'Ошибка получения всех категорий с портала VTT <br>' . $E->faultstring;
                return false;
            }
            $data['total'] = $result->GetItemPortionResult->TotalCount;

            return $data['total'];
        } else {
            return false;
        }
    }

    public function getMainCategories () {
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
            $main_categories = array();

            try {
                $result = $this->client->GetCategories($params);
            } catch (SoapFault $E) {
                echo $E->faultstring;
                return false;
            }

            $items = is_array($result->GetCategoriesResult->CategoryDto)
                ? $result->GetCategoriesResult->CategoryDto
                : array($result->GetCategoriesResult->CategoryDto);
            foreach ($items as $category) {
                if ($category->ParentId == null) {
                    $main_categories[] = array(
                        'name' => $category->Name,
                        'id' => $category->Id
                    );
                }
            }
            return $main_categories;
        } else {
            return false;
        }
    }

    public function getAllCategories () {
        // Функция получения списка категорий с портала ВТТ

        global $ERROR;
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD);
            try {
                $result = $this->client->GetCategories($params);
            } catch (SoapFault $E) {
                //echo $E->faultstring;
                $ERROR['VTT'][] = 'Ошибка получения всех категорий с портала VTT <br>' . $E->faultstring;
                return false;
            }

            $all_categories = is_array($result->GetCategoriesResult->CategoryDto)
                ? $result->GetCategoriesResult->CategoryDto
                : array($result->GetCategoriesResult->CategoryDto);
            $result = array();
            foreach ($all_categories as $category) {
                $result[] = array(
                    'name' => $category->Name,
                    'id' => $category->Id,
                    'parent_id' => $category->ParentId
                );
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getProductPortion($from, $to) {
        // Функция получения порции товаров с портала ВТТ

        global $ERROR;
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD, "from" => $from, "to" => $to);
            try {
                $result = $this->client->GetItemPortion($params);
            } catch (SoapFault $E) {
                //echo $E->faultstring;
                $ERROR['VTT'][] = 'Ошибка получения всех категорий с портала VTT <br>' . $E->faultstring;
                return false;
            }
            $data['total'] = $result->GetItemPortionResult->TotalCount;
            $data['products'] = is_array($result->GetItemPortionResult->Items->ItemDto)
                ? $result->GetItemPortionResult->Items->ItemDto
                : array($result->GetItemPortionResult->Items->ItemDto);

            return $data;
        } else {
            return false;
        }
    }

    public function getProductByCategory($cat_id) {
        // Функция получения товаров из указанной категории и всех подкатегорий с портала ВТТ

        global $ERROR;
        if ($this->status) {
            $params = array("login" => VTT_LOGIN , "password" => VTT_PASSWORD, "categoryId" => $cat_id);
            try {
                $result = $this->client->GetCategoryItems($params);
            } catch (SoapFault $E) {
                //echo $E->faultstring;
                $ERROR['VTT'][] = 'Ошибка получения товаров по категориям с портала VTT: ' . $cat_id;
                return false;
            }

            $products = is_array($result->GetCategoryItemsResult->ItemDto)
                ? $result->GetCategoryItemsResult->ItemDto
                : array($result->GetCategoryItemsResult->ItemDto);

            return $products;
        } else {
            return false;
        }
    }

    public function getAllProductByCategory() {
        // Функция получения товаров с портала ВТТ через метод получения товаров по категориям
        // Получение товаров происходит методом получения товаров по главным подкатегориям главных категорий
        // чтобы уменьшить нагрузку на сервер и получать товары более меньшими порциями

        global $ERROR;

        $main_cat = $this->getMainCategories();
        $main_cat_array = array();
        foreach ($main_cat as $cat) {
            $main_cat_array[] = $cat['id'];
        }
        $all_categories = $this->getAllCategories();
        $products = array();
        $error_cats = array();
        foreach ($all_categories as $category) {
            if (!$this->isCategoryExcept($all_categories, $category)) {
                if (in_array($category['parent_id'], $main_cat_array)) {
                    $result  = $this->getProductByCategory($category['id']);
                    if ($result) {
                        foreach ($result as $product) {
                            $products[] = $product;
                        }
                    } else {
                        $error_cats[] = $category['id'];
                    }

                }
            }
        }
        // Проверяем, остались ли категории, по которым не удалось получить товары и пробуем их получить
        // Количество попыток определяется переменной $steps
        $steps = 10;
        $step = 0;
        while ($step < $steps) {
            if (!empty($error_cats)) {
                foreach ($error_cats as $key => $cat) {
                    $result  = $this->getProductByCategory($cat);
                    if ($result) {
                        foreach ($result as $product) {
                            $products[] = $product;
                        }
                        unset($error_cats[$key]);
                    }
                }
            }
            $step++;
        }

        //
        if (empty($error_cats)) {
            $prod_array = array();
            foreach ($products as &$product) {
                $prod_array[] = array(
                    'available_quantity' => $product->AvailableQuantity,
                    'brand' => $product->Brand,
                    'color_name' => $product->ColorName,
                    'compatibility' => $product->Compatibility,
                    'depth' => $product->Depth,
                    'description' => $product->Description,
                    'group' => $product->Group,
                    'height' => $product->Height,
                    'id' => $product->Id,
                    'item_life_time' => $product->ItemLifeTime,
                    'main_office_quantity' => $product->MainOfficeQuantity,
                    'name' => $product->Name,
                    'original_number' => $product->OriginalNumber,
                    'photo_url' => $product->PhotoUrl,
                    'price' => $product->Price,
                    'root_group' => $product->RootGroup,
                    'transit_date' => $product->TransitDate,
                    'transit_quantity' => $product->TransitQuantity,
                    'vendor' => $product->Vendor,
                    'weight' => $product->Weight,
                    'width' => $product->Width
                );
            }
            return $prod_array;
        } else {
            return false;
        }
    }

    public function createCategory () {
        // Функция создает новые категории в ПУСТОЙ базе для формирования прайса
        // Создает карту категорий, категории поставщика и категории в нашей базе
        // Затем функция обновляет родительские категории и устанавливает главным
        // категориям поставшика категорию нашего прайса id = 542 (ЗИП для Оргтехники),
        // делая их дочерними.

        global $ERROR;
        if ($this->status) {
            $categories = $this->getAllCategories();
            if ($categories) {
                $db = new Db;
                if ($db == false) {
                    return false;
                }
                foreach ($categories as $category) {
                    // Если категория не входит в исключение из загрузки
                    if (!$this->isCategoryExcept($categories, $category)) {
                        // Добавляем категорию в таблицу категорий поставщиков
                        $data = array();
                        $data['provider_id'] = 1;
                        $data['provider_category_id'] = $category['id'];
                        $data['provider_category_name'] = $category['name'];
                        $data['provider_category_parent_id'] = $category['parent_id'];
                        $our_provider_cat_id = $db->addProviderCategory($data);

                        // Добавляем категорию в нашу базу категорий
                        $data = array();
                        $data['name'] = $category['name'];
                        $data['parent_id'] = $category['parent_id'];
                        $our_cat_id = $db->addCategory($data);

                        // Добавляем запись в таблицу сопоставления категорий
                        if ($our_cat_id AND $our_provider_cat_id) {
                            $cat_map_id = $db->addMap('category', $our_cat_id, $our_provider_cat_id);
                        }
                    }
                }
                // Обновляем родительские категории в нашей БД на id наших категорий
                foreach ($categories as $category) {
                    if (!$this->isCategoryExcept($categories, $category)) {
                        if ($category['parent_id'] != '') {
                            $our_cat_id = $db->getOurItemIdByProvItemId('category', $category['id'], 1);
                            $our_parent_cat_id = $db->getOurItemIdByProvItemId('category', $category['parent_id'], 1);
                            if ($our_cat_id AND $our_parent_cat_id) {
                                $data = array();
                                $data['name'] = $category['name'];
                                $data['parent_id'] = $our_parent_cat_id;
                                $db->editCategory($our_cat_id, $data);
                            }
                        } else {
                            // Помещаем все родительские категории в подкатегорию нашей базы
                            $our_cat_id = $db->getOurItemIdByProvItemId('category', $category['id'], 1);
                            if ($our_cat_id) {
                                $data['name'] = $category['name'];
                                $data['parent_id'] = 542;
                                $db->editCategory($our_cat_id, $data);
                            }
                        }
                    }
                }


                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function createManufacturer ($products) {
        // Функция формирует новых производителей в ПУСТОЙ базе на основе входного параметра
        // массива $products в формате поставщика (ВТТ)
        // Создает карту производителей, производителей поставщика и производителей в нашей базе
        //
        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $manufacturers = array();
            $db = new Db;
            if ($db == false) {
                return false;
            }
            // Формируем массив имен производителей
            foreach ($products as &$product) {
                if ($product['brand'] != '') {
                    if (!in_array($product['brand'], $manufacturers)) {
                        $manufacturers[] = $product['brand'];
                    }
                }
            }
            // Если массив производителей сформирован, записываем производителей
            if (!empty($manufacturers)) {
                foreach ($manufacturers as $manufacturer) {
                    // Добавляем производителей в таблицу поставщиков
                    $data = array();
                    $data['provider_id'] = $prov_id;
                    $data['name'] = $manufacturer;
                    $our_prov_manuf_id = $db->addProviderManufacturer($data);

                    // Добавляем производителей в нашу таблицу БД
                    $data = array();
                    $data['name'] = $manufacturer;
                    $our_manuf_id = $db->addManufacturer($data);

                    // Добавляем запись в таблицу сопоставления категорий
                    if ($our_manuf_id AND $our_prov_manuf_id) {
                        $manuf_map_id = $db->addMap('manufacturer', $our_manuf_id, $our_prov_manuf_id);
                    } else {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function createModel ($products) {
        // Функция формирует новые модели продуктов в ПУСТОЙ базе на основе входного параметра
        // массива $products в формате имен ключей поставщика (ВТТ)
        // Создает карту моделей, моделей поставщика и моделей в нашей базе
        //
        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $models = array();
            $db = new Db;
            if ($db == false) {
                return false;
            }
            // Формируем массив имен моделей
            foreach ($products as &$product) {
                if ($product['original_number'] != '') {
                    if (!in_array($product['original_number'], $models)) {
                        $models[] = $product['original_number'];
                    }
                }
            }
            // Если массив моделей сформирован, записываем модели
            if (!empty($models)) {
                foreach ($models as $model) {
                    // Добавляем модели в таблицу поставщиков
                    $data = array();
                    $data['provider_id'] = $prov_id;
                    $data['name'] = $model;
                    $our_prov_model_id = $db->addProviderModel($data);

                    // Добавляем модели в нашу таблицу БД
                    $data = array();
                    $data['name'] = $model;
                    $our_model_id = $db->addModel($data);

                    // Добавляем запись в таблицу сопоставления категорий
                    if ($our_model_id AND $our_prov_model_id) {
                        $model_map_id = $db->addMap('model', $our_model_id, $our_prov_model_id);
                    } else {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function createVendor ($products) {
        // Функция формирует новых Вендоров в ПУСТОЙ базе на основе входного параметра
        // массива $products в формате имен ключей поставщика (ВТТ)
        // Создает карту вендоров, вендоров поставщика и вендоров в нашей базе
        //
        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $vendors = array();
            $db = new Db;
            if ($db == false) {
                return false;
            }
            // Формируем массив имен вендоров
            foreach ($products as &$product) {
                if ($product['vendor'] != '') {
                    if (!in_array($product['vendor'], $vendors)) {
                        $vendors[] = $product['vendor'];
                    }
                }
            }
            // Если массив вендоров сформирован, записываем модели
            if (!empty($vendors)) {
                foreach ($vendors as $vendor) {
                    // Добавляем вендоров в таблицу поставщиков
                    $data = array();
                    $data['provider_id'] = $prov_id;
                    $data['name'] = $vendor;
                    $our_prov_vendor_id = $db->addProviderVendor($data);

                    // Добавляем вендоров в нашу таблицу БД
                    $data = array();
                    $data['name'] = $vendor;
                    $our_vendor_id = $db->addVendor($data);

                    // Добавляем запись в таблицу сопоставления
                    if ($our_vendor_id AND $our_prov_vendor_id) {
                        $vendor_map_id = $db->addMap('vendor', $our_vendor_id, $our_prov_vendor_id);
                    } else {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function createAttribute ($products) {
        // Функция формирует значения аттрибутов в ПУСТОЙ базе
        // (цвет и ресурс) на основе входного параметра
        // массива $products в формате имен ключей поставщика (ВТТ)
        // Создает карту значений аттрибутов, группы атрибутов, аттрибуты
        // и значения в таблицах поставщика и нашей базы
        //
        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $default_attr_group_name = 'Основные';
            $default_attr_names = array('Цвет', 'Ресурс');
            $vendors = array();
            $db = new Db;
            if ($db == false) {
                return false;
            }
            // Создаем группу аттрибутов поставщика по умолчанию
            $data = array();
            $data['provider_id'] = $prov_id;
            $data['name'] = $default_attr_group_name;
            $our_prov_attrib_group_id = $db->addProviderAttributeGroup($data);

            // Создаем группу аттрибутов в нашей базе по умолчанию
            $data = array();
            $data['name'] = $default_attr_group_name;
            $our_attrib_group_id = $db->addAttributeGroup($data);

            // Создаем аттрибуты
            foreach ($default_attr_names as $attribute_name) {
                // Добавляем аттрибуты в таблицу поставщиков
                $data = array();
                $data['provider_id'] = $prov_id;
                $data['name'] = $attribute_name;
                $data['group_id'] = $our_prov_attrib_group_id;
                $our_prov_attrib_id = $db->addProviderAttribute($data);

                // Добавляем аттрибуты в таблицу нашей БД
                $data = array();
                $data['name'] = $attribute_name;
                $data['group_id'] = $our_attrib_group_id;
                $our_attrib_id = $db->addAttribute($data);
            }

            // Формируем массивы значений аттрибутов
            $colors = array();
            $lifestimes = array();
            foreach ($products as &$product) {
                // Проверяем аттрибут "цвет"
                if ($product['color_name'] != '') {
                    if (!in_array($product['color_name'], $colors)) {
                        $colors[] = $product['color_name'];
                    }
                }
                // Проверяем аттрибут "ресурс"
                if ($product['item_life_time'] != '') {
                    if (!in_array($product['item_life_time'], $lifestimes)) {
                        $lifestimes[] = $product['item_life_time'];
                    }
                }
            }
            // Если массивы значений аттрибутов сформированы, записываем значения аттрибутов по очереди
            if (!empty($colors) AND !empty($lifestimes)) {
                // записываем значения аттрибута "цвет"
                foreach ($colors as $color) {
                    // Добавляем значение аттрибута в таблицу поставщиков
                    $data = array();
                    $attribute_name = 'Цвет'; // Имя аттрибута из массива заданного в начале функции
                    $data['provider_id'] = $prov_id;
                    $data['attribute_id'] = $db->getProviderAttributeIdByName($prov_id, $attribute_name, $default_attr_group_name);
                    $data['value'] = $color;
                    $our_prov_attrib_value_id = $db->addProviderAttributeValue($data);

                    // Добавляем значение аттрибута в нашу таблицу БД
                    $data = array();
                    $attribute_name = 'Цвет'; // Имя аттрибута из массива заданного в начале функции
                    $data['attribute_id'] = $db->getAttributeIdByName($attribute_name, $default_attr_group_name);
                    $data['value'] = $color;
                    $our_attrib_value_id = $db->addAttributeValue($data);

                    // Добавляем запись в таблицу сопоставления
                    if ($our_attrib_value_id AND $our_prov_attrib_value_id) {
                        $attrib_value_id = $db->addMap('attrib_value', $our_attrib_value_id, $our_prov_attrib_value_id);
                    } else {
                        return false;
                    }
                }

                // записываем значения аттрибута "ресурс"
                foreach ($lifestimes as $lifetime) {
                    // Добавляем значение аттрибута в таблицу поставщиков
                    $data = array();
                    $attribute_name = 'Ресурс'; // Имя аттрибута из массива заданного в начале функции
                    $data['provider_id'] = $prov_id;
                    $data['attribute_id'] = $db->getProviderAttributeIdByName($prov_id, $attribute_name, $default_attr_group_name);
                    $data['value'] = $lifetime;
                    $our_prov_attrib_value_id = $db->addProviderAttributeValue($data);

                    // Добавляем значение аттрибута в нашу таблицу БД
                    $data = array();
                    $attribute_name = 'Ресурс'; // Имя аттрибута из массива заданного в начале функции
                    $data['attribute_id'] = $db->getAttributeIdByName($attribute_name, $default_attr_group_name);
                    $data['value'] = $color;
                    $our_attrib_value_id = $db->addAttributeValue($data);

                    // Добавляем запись в таблицу сопоставления
                    if ($our_attrib_value_id AND $our_prov_attrib_value_id) {
                        $attrib_value_id = $db->addMap('attrib_value', $our_attrib_value_id, $our_prov_attrib_value_id);
                    } else {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function checkTotalProductByVtt($my_total) {
        // Функция проверки количества товаров на портале ВТТ с количеством переданным в виде параметра
        // Функция возвращает истину или количество товаров в запросе с портала ВТТ
        // Функция считает общее количество товаров минус количество товаров исключений, и это же значение
        // возвращается, если оно отличается от переданного значения для проверки
        $total = $this->getTotalProductByProdPortion();
        $total_except = 0;
        if (!empty(VTT_CATEGORY_ID_EXCEPT)) {
            foreach (VTT_CATEGORY_ID_EXCEPT as $cat) {
                $count = 0;
                $count = $this->getTotalProductByCategoryId($cat);
                if ($count) {
                    $total_except += $count;
                }
            }
        }
        if ($my_total == ($total - $total_except)) {
            return true;
        } else {
            //$total = $total - $total_except;
            return false;
        }
    }

    public function isCategoryExcept ($categories, $category) {
        // Функция проверки категории на исключение из загрузки
        // Исключения задаются в конфигурационном файле указанием ID категорий
        // поставщика
        // Рекурсивно проверяем все вышестоящие категории (категории-родители)
        // на исключение
        // Функция принимает два значения:
        // 1) $categories - массив из категорий (Std объектов)
        // 2) $category - проверяемая категория (Std объект)

        if (in_array($category['id'], VTT_CATEGORY_ID_EXCEPT)) {
            return true;
        } elseif ($category['parent_id'] != '') {
            foreach ($categories as $parent_cat) {
                if ($parent_cat['id'] == $category['parent_id']) {
                    if ($this->isCategoryExcept($categories, $parent_cat)) {
                        return true;
                    } else {
                        $this->isCategoryExcept($categories, $parent_cat);
                    }
                }
            }

        } else {
            return false;
        }
    }


}
