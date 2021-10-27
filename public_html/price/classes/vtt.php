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
            //
            $prod_array = array();
            foreach ($data['products'] as &$product) {
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
            $data['products'] = $prod_array;

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
        $steps = 20;
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
        $prov_id = 1;
        $our_root_category_for_vtt_id = 542; // id родительской категории для категорий с ВТТ

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
                        $data['provider_id'] = $prov_id;
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
                                $data['parent_id'] = $our_root_category_for_vtt_id;
                                $db->editCategory($our_cat_id, $data);
                            }
                        }
                    }
                }

                // Создаем ИМЕНА родительских категорий в таблице категорий поставщика
                // т.к. в выгрузке передается только id родительской категории, без имени
                foreach ($categories as $category) {
                    if (!$this->isCategoryExcept($categories, $category)) {
                        if ($category['parent_id'] != '') {
                            $prov_parent_cat = $db->getProvCatByProvCatId($prov_id, $category['parent_id']);
                            if ($prov_parent_cat) {
                                $data = array();
                                $prov_cat_id = $db->getCatIdByProvCatId($prov_id, $category['id']);
                                $data['provider_id'] = $prov_id;
                                $data['provider_category_id'] = $category['id'];
                                $data['provider_category_name'] = $category['name'];
                                $data['provider_category_parent_id'] = $category['parent_id'];
                                $data['provider_parent_cat_name'] = $prov_parent_cat['name'];
                                $db->editProviderCategory($prov_cat_id, $data);
                            }
                        }
                    }
                }



                //

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
                    $data['attribute_id'] = $db->getOurProviderAttributeIdByName($prov_id, $attribute_name, $default_attr_group_name);
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
                    $data['attribute_id'] = $db->getOurProviderAttributeIdByName($prov_id, $attribute_name, $default_attr_group_name);
                    $data['value'] = $lifetime;
                    $our_prov_attrib_value_id = $db->addProviderAttributeValue($data);

                    // Добавляем значение аттрибута в нашу таблицу БД
                    $data = array();
                    $attribute_name = 'Ресурс'; // Имя аттрибута из массива заданного в начале функции
                    $data['attribute_id'] = $db->getAttributeIdByName($attribute_name, $default_attr_group_name);
                    $data['value'] = $lifetime;
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

    public function createProduct ($products) {
        // Функция формирует новые товары в ПУСТОЙ базе на основе входного параметра
        // массива $products в формате имен ключей поставщика (ВТТ)
        // Создает карту продуктов, продуктов поставщика и продуктов в нашей базе
        //
        global $ERROR;

        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $vendors = array();

            // Создаем объект БД для записи данных
            $db = new Db;
            if ($db == false) {
                return false;
            }
            // Получаем данные по каждому продукту и производим запись в базу
            $count_add_product = 0; // устанавливаем счетчик количества добавленных товаров
            foreach ($products as &$product) {
                // Получаем данные и производим запись в таблицу Поставщика
                $data = array();

                // Получаем id поставщика товара
                $data['provider_id'] = $prov_id;

                // Получаем id поставщика товара
                if ($product['id'] != '') $data['provider_product_id'] = $product['id']; else {
                    $ERROR['VTT'][] = 'Ошибка получения id товара с ВТТ' .
                    '<br>id продукта: ' . $product['id'] .
                    '<br>name продукта: ' . $product['name'];
                    continue;
                }

                // Получаем имя товара
                if ($product['name'] != '') $data['name'] = $product['name']; else {
                    $ERROR['VTT'][] = 'Ошибка получения name товара с ВТТ' .
                        '<br>id продукта: ' . $product['id'] .
                        '<br>name продукта: ' . $product['name'];
                    continue;
                }

                // Получаем описание товара
                if ($product['description'] != '') {
                    $data['description'] = $product['description'];
                }

                // Получаем ширину товара
                if ($product['width'] != '') {
                    $data['width'] = $product['width'];
                }

                // Получаем высоту товара
                if ($product['height'] != '') {
                    $data['height'] = $product['height'];
                }

                // Получаем длину (глубину) товара
                if ($product['depth'] != '') {
                    $data['length'] = $product['depth'];
                }

                // Получаем вес товара
                if ($product['weight'] != '') {
                    $data['weight'] = $product['weight'];
                }

                // Получаем id категории товара в таблице поставщика
                $id_prov_cat_id = $db->getCatIdByProvCatName($prov_id, $product['group'], $product['root_group']);
                if ($id_prov_cat_id) $data['category_id'] = $id_prov_cat_id; else {
                    $ERROR['VTT'][] = 'Ошибка получения id категории поставщика по имени категории и родительской категории при импорте товаров с ВТТ' .
                        '<br>id продукта: ' . $product['id'] .
                        '<br>name продукта: ' . $product['name'];
                    continue;
                }

                // Получаем id модели (парт-номера) товара в таблице поставщика
                if ($product['original_number'] != '') {
                    $model_id  = $db->getModelIdByProvModelName($prov_id, $product['original_number']);
                    if ($model_id) {
                        $data['model_id'] = $model_id;
                    } else {
                        $ERROR['VTT'][] = 'Ошибка получения model_id товара поставщика по имени модели при импорте товаров с ВТТ' .
                            '<br>id продукта: ' . $product['id'] .
                            '<br>name продукта: ' . $product['name'];
                        continue;
                    }
                }

                // Получаем id вендора товара в таблице поставщика
                if ($product['vendor'] != '') {
                    $vendor_id  = $db->getVendorIdByProvVendorName($prov_id, $product['vendor']);
                    if ($vendor_id) {
                        $data['vendor_id'] = $vendor_id;
                    } else {
                        $ERROR['VTT'][] = 'Ошибка получения vendor_id товара поставщика по имени вендора при импорте товаров с ВТТ' .
                            '<br>id продукта: ' . $product['id'] .
                            '<br>name продукта: ' . $product['name'];
                        continue;
                    }
                }

                // Получаем id производителя товара в таблице поставщика
                if ($product['brand'] != '') {
                    $manuf_id  = $db->getManufIdByProvManufName($prov_id, $product['brand']);
                    if ($manuf_id) {
                        $data['manufacturer_id'] = $manuf_id;
                    } else {
                        $ERROR['VTT'][] = 'Ошибка получения manufacturer_id товара поставщика по имени производителя (Брэнда) при импорте товаров с ВТТ' .
                            '<br>id продукта: ' . $product['id'] .
                            '<br>name продукта: ' . $product['name'];
                        continue;
                    }
                }

                // Добавляем товар в таблицу поставщиков
                $id_prov_product = $db->addProviderProduct($data);

                // Проверяем, если запись товара произведена успешно, то увеличиваем счетчик
                // добавленных товаров и производим загрузку остальных данных по товару
                if ($id_prov_product) {
                    $count_add_product++;

                    // Производим запись аттрибутов для добавленного товара
                    // аттрибут "Цвет"
                    if ($product['color_name'] != '') {
                        $attrib_id = $db->getOurProviderAttributeIdByName($prov_id, 'Цвет', 'Основные');
                        if ($attrib_id) $attrib_value_id = $db->getOurProviderAttributeValueIdByValue($prov_id, $attrib_id, $product['color_name']);
                        if ($attrib_value_id) {
                            $data = array();
                            $data['product_id'] = $id_prov_product;
                            $data['attribute_value_id'] = $attrib_value_id;
                            $id_attrib_product = $db->addProviderAttributeProduct($data);
                        }
                    }
                    // аттрибут "Ресурс"
                    if ($product['item_life_time'] != '') {
                        $attrib_id = $db->getOurProviderAttributeIdByName($prov_id, 'Ресурс', 'Основные');
                        if ($attrib_id) $attrib_value_id = $db->getOurProviderAttributeValueIdByValue($prov_id, $attrib_id, $product['item_life_time']);
                        if ($attrib_value_id) {
                            $data = array();
                            $data['product_id'] = $id_prov_product;
                            $data['attribute_value_id'] = $attrib_value_id;
                            $id_attrib_product = $db->addProviderAttributeProduct($data);
                        }
                    }
                    // Производим запись изображений для добавленного товара
                    if ($product['photo_url']) {
                        $data = array();
                        $data['provider_id'] = $prov_id;
                        $data['product_id'] = $id_prov_product;
                        $data['image'] = $product['photo_url'];
                        $id_image_product = $db->addProviderProductImage($data);
                    }
                }



                //$id_our_cat_id = $db->getOurItemIdByProvItemId('category', $id_prov_cat_id, $prov_id);
            }

            echo 'Количество товаров добавленных в таблицу поставщика: '. $count_add_product . '<br>';

            return true;
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
        if ($my_total === ($total - $total_except)) {
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

    public function updateProductsTotal() {
        if ($this->status) {
            $prov_id = 1; // устанавливаем id поставщика
            $db = new Db;
            if ($db == false) {
                return false;
            }
            //
            $products_vtt = $this->getAllProductByCategory();

            $product_vtt_base_count = count($products_vtt);
            $product_our_base_count = $db->getProviderProductCount($prov_id);

            if ($product_vtt_base_count == $product_our_base_count) echo 'Количество продуктов в нашей БД и на портале поставщика совпадает и равно: ' . $product_our_base_count; else {
                echo 'Внимание! Разное количество продуктов в нашей БД и на портале ВТТ!<br>';
                echo 'Товаров в нашей БД: ' . $product_our_base_count . '<br>';
                echo 'Товаров на портале ВТТ: ' . $product_vtt_base_count . '<br>';
                echo 'Производим попытку обновления совпадающих товаров...<br>';
            }

            $provider_product_total_count_add = 0;
            $provider_product_total_count_edit = 0;
            $provider_product_total_count_update = 0;
            foreach ($products_vtt as $product_vtt) {
                $data = array();
                // Получаем id продукта поставщика в нашей базе по id товара поставщика
                $product_id = $db->getOurProviderProductIdByProviderProductId($prov_id, $product_vtt['id']);
                // Проверяем, есть ли товар в базе
                if ($product_id) {
                    $product_total = $db->getProviderProductTotal($prov_id, $product_id);
                    // Проверяем, есть ли данные по количеству и цене в базе по продукту
                    if ($product_total) {
                        // Сравниваем данные по количеству, цене и транзиту
                        if ($product_total['total'] != intval($product_vtt['main_office_quantity']))
                            $data['total'] = intval($product_vtt['main_office_quantity']);
                        //
                        if ($product_total['price_usd'] != floatval($product_vtt['price']))
                            $data['price_usd'] = floatval($product_vtt['price']);
                        //
                        if ($product_total['transit'] != intval($product_vtt['transit_quantity']))
                            $data['transit'] = intval($product_vtt['transit_quantity']);
                        //
                        if ($product_total['transit_date'] != $product_vtt['transit_date'])
                            $data['transit_date'] = $product_vtt['transit_date'];
                        //
                        // Проверяем, были ли какие либо изменения
                        if (!empty($data)) {
                            // если данные по цене и количеству продукта в базе отличаются от полученных данных
                            // то проверяем какие и записываем изменения
                            $data['provider_id'] = $prov_id;
                            $data['product_id'] = $product_id;

                            if (!isset($data['total'])) $data['total'] = $product_total['total'];
                            if (!isset($data['price_usd'])) $data['price_usd'] = $product_total['price_usd'];
                            if (!isset($data['transit'])) $data['transit'] = $product_total['transit'];
                            if (!isset($data['transit_date'])) $data['transit_date'] = $product_total['transit_date'];

                            $product_total_edits_id = $db->editProviderProductTotal($product_id, $data);
                            if ($product_total_edits_id) $provider_product_total_count_edit++;
                        } else {
                            // если изменений по количеству и цене нет, то просто обновляем
                            // дату проверки (обновления) у товара
                            $product_total_updates = $db->updateProviderProductTotal($prov_id, $product_id);
                            if ($product_total_updates) $provider_product_total_count_update++;
                        }
                    } else {
                        $data['provider_id'] = $prov_id;
                        $data['product_id'] = $product_id;
                        $data['total'] = intval($product_vtt['main_office_quantity']);
                        $data['price_usd'] = floatval($product_vtt['price']);
                        $data['transit'] = intval($product_vtt['transit_quantity']);
                        $data['transit_date'] = $product_vtt['transit_date'];

                        $product_total_adds_id = $db->addProviderProductTotal($data);
                        if ($product_total_adds_id) $provider_product_total_count_add++;
                    }

                } else {
                    echo 'Товар отсутствует в нашей БД<br>';
                    echo '<pre>';
                    print_r($product_vtt);
                    echo '</pre>';
                    echo '<br>';
                    continue;
                }
            }
            echo 'Добавлено значений цены, остатков и транзита для ' . $provider_product_total_count_add . ' товаров<br>';
            echo 'Изменено значений цены, остатков и транзита для ' . $provider_product_total_count_edit . ' товаров<br>';
            echo 'Обновлено значений цены, остатков и транзита для ' . $provider_product_total_count_update . ' товаров<br>';
            return true;
        } else return false;
    }


}
