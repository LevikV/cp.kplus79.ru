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
                $ERROR['VTT'][] = 'Ошибка получения всех категорий с портала VTT <br>' . $E->faultstring;
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
