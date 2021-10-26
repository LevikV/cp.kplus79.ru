<?php

class Db {
    public $status;
    private $link;

    function __construct()
    {
        //mysqli_report(MYSQLI_REPORT_ALL);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        global $ERROR;
        try {
            $this->link = mysqli_connect(DB_SERVER, DB_USER, DB_PSWD, DB_NAME);
            if ($this->link != false) {
                $this->status = true;
            }
        } catch (Exception $e) {
            $ERROR['Db'][] = 'Ошибка создания подключения к БД';
            $this->status = false;
        }
    }

    public function getCategories() {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT * FROM category ';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка получения всех наших категорий';
                return false;
            }
            if ($result != false) {
                $rows = array();
                while($row = $result->fetch_array()){
                    $rows[] = array(
                        'id' => $row["id"],
                        'name' => $row["name"],
                        'description' => $row["description"],
                        'parent_id' => $row["parent_id"],
                        'image' => $row["image"]
                    );
                }
                return $rows;
            }
        } else {
            return false;
        }
    }

    public function getProviderProductCount($prov_id) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT COUNT(*) FROM provider_product WHERE provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка получения количества товаров поставщика из нашей БД по pro_id' .
                    '<br>prov_id: ' . $prov_id;
                return false;
            }
            if ($result != false) {
                //$row = $result->fetch_row();
                //$row = mysqli_fetch_array($result);
                $row = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $count = $row[0]['COUNT(*)'];
                return (int)$count;
            }
        } else {
            return false;
        }
    }

    public function getAttributeIdByName($attribute_name, $attribute_group_name) {
        // Функция поиска id аттрибута в нашей базе по имени и имени группы атрибута
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM attribute WHERE name = "' . $attribute_name .
                '" AND group_id = (SELECT id FROM attribute_group WHERE name = "' . $attribute_group_name . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска аттрибута по имени в нашей базе.' .
                    '<br>attribute_name: ' . $attribute_name .
                    '<br>attribute_group_name: ' . $attribute_group_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $our_prov_attrib_id = $row[0];
                return $our_prov_attrib_id;
            }
        } else {
            return false;
        }
    }

    public function getOurItemIdByProvItemId($code, $prov_item_id, $prov_id) {
        global $ERROR;
        if ($this->status) {
            switch ($code) {
                case 'category': {
                    $sql = 'SELECT our_id FROM map WHERE code = "'. $code . '" AND provider_id = (SELECT id FROM provider_category WHERE provider_category_id = "' . $prov_item_id . '" AND provider_id = ' . (int)$prov_id .')';
                }
            }

            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска категории для сопоставления.' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>code: ' . $code .
                    '<br>prov_cat_id: ' . $prov_item_id;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $our_cat_id = $row[0];
                return $our_cat_id;
            }
        } else {
            return false;
        }
    }

    public function getCatIdByProvCatName($prov_id, $prov_cat_name, $prov_root_cat_name) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_category WHERE name = "' . $prov_cat_name .
                '" AND provider_id = '. (int)$prov_id . ' AND provider_parent_cat_name = "' . $prov_root_cat_name . '"';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска категории для сопоставления.' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_cat_name: ' . $prov_cat_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $our_cat_id = $row[0];
                return $our_cat_id;
            }
        } else {
            return false;
        }
    }

    public function getModelIdByProvModelName($prov_id, $prov_model_name) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_model WHERE name = "' . $prov_model_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска id модели по имени модели поставщика' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_model_name: ' . $prov_model_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $model_id = $row[0];
                return $model_id;
            }
        } else {
            return false;
        }
    }

    public function getVendorIdByProvVendorName($prov_id, $prov_vendor_name) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_vendor WHERE name = "' . $prov_vendor_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска id вендора по имени вендора поставщика' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_vendor_name: ' . $prov_vendor_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $vendor_id = $row[0];
                return $vendor_id;
            }
        } else {
            return false;
        }
    }

    public function getManufIdByProvManufName($prov_id, $prov_manuf_name) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_manufacturer WHERE name = "' . $prov_manuf_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска id производителя (брэнда) по имени брэнда поставщика' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_manuf_name: ' . $prov_manuf_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $manuf_id = $row[0];
                return $manuf_id;
            }
        } else {
            return false;
        }
    }

    public function getProvCatByProvCatId($prov_id, $prov_cat_id) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT * FROM provider_category WHERE provider_category_id = "' . $prov_cat_id .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка получения категории поставщика по provCatId' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_cat_id: ' . $prov_cat_id;
                return false;
            }
            if ($result != false) {
                //$row = $result->fetch_row();
                //$row = mysqli_fetch_array($result);
                $row = mysqli_fetch_all($result, MYSQLI_ASSOC);

                return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getCatIdByProvCatId($prov_id, $prov_cat_id) {
        //
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_category WHERE provider_category_id = "' . $prov_cat_id .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска id категории по id категории поставщика' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_cat_id: ' . $prov_cat_id;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $our_cat_id = $row[0];
                return $our_cat_id;
            }
        } else {
            return false;
        }
    }

    public function getOurProviderAttributeIdByName($prov_id, $attribute_name, $attribute_group_name) {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_attribute WHERE provider_id = ' . (int)$prov_id . ' AND name = "' . $attribute_name .
                '" AND group_id = (SELECT id FROM provider_attribute_group WHERE provider_id = ' . (int)$prov_id . ' AND name = "' . $attribute_group_name . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска аттрибута поставщика по имени.' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>attribute_name: ' . $attribute_name .
                    '<br>attribute_group_name: ' . $attribute_group_name;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $our_prov_attrib_id = $row[0];
                return $our_prov_attrib_id;
            }
        } else {
            return false;
        }
    }

    public function getOurProviderAttributeValueIdByValue($prov_id, $attrib_id, $value) {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_attribute_value WHERE provider_id = ' . (int)$prov_id . ' AND attribute_id = ' . (int)$attrib_id .
                ' AND value = "' . $value . '"';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска id значения аттрибута поставщика по id аттрибута и его значению' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>attribute_id: ' . $attrib_id .
                    '<br>attribute_value: ' . $value;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $attrib_value_id = $row[0];
                return $attrib_value_id;
            }
        } else {
            return false;
        }
    }

    public function getOurProviderProductIdByProviderProductId($prov_id, $prov_product_id) {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT id FROM provider_product WHERE provider_id = ' . (int)$prov_id . ' AND provider_product_id = "' . $prov_product_id . '"';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска товара поставщика в таблице поставщиков по provider_id и provider_product_id' .
                    '<br>prov_id: ' . $prov_id .
                    '<br>prov_product_id: ' . $prov_product_id;
                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                $prov_product_id = $row[0];
                return $prov_product_id;
            }
        } else {
            return false;
        }
    }

    public function addProduct($data) {
        if ($this->status) {

        }
    }

    public function addCategory($data) {
        global $ERROR;
        if ($this->status AND $this->checkCategoryData($data)) {
            $sql = 'INSERT INTO category (name, description, parent_id, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['parent_id'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления категории в нашу базу категорий.' .
                    '<br>name: ' . $data['name'] .
                    '<br>description: ' . $data['description'] .
                    '<br>parent_id: ' . $data['parent_id'] .
                    '<br>image: ' . $data['image'];
                return false;
            }
            if ($result != false) {
                $category_id = mysqli_insert_id($this->link);
                return $category_id;
            }
        } else {
            return false;
        }
    }

    public function addManufacturer($data) {
        global $ERROR;
        if ($this->status AND $this->checkManufacturerData($data)) {
            $sql = 'INSERT INTO manufacturer (name, description, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления производителя в нашу таблицу производителей' .
                    '<br>name: ' . $data['name'] .
                    '<br>description: ' . $data['description'] .
                    '<br>image: ' . $data['image'];
                return false;
            }
            if ($result != false) {
                $manufacturer_id = mysqli_insert_id($this->link);
                return $manufacturer_id;
            }
        } else {
            return false;
        }
    }

    public function addModel($data) {
        global $ERROR;
        if ($this->status AND $this->checkModelData($data)) {
            $sql = 'INSERT INTO model (name) VALUES ("' .
                $data['name'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления модели в нашу таблицу моделей' .
                    '<br>name: ' . $data['name'];
                return false;
            }
            if ($result != false) {
                $model_id = mysqli_insert_id($this->link);
                return $model_id;
            }
        } else {
            return false;
        }
    }

    public function addVendor($data) {
        global $ERROR;
        if ($this->status AND $this->checkVendorData($data)) {
            $sql = 'INSERT INTO vendor (name, description, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления вендора в нашу таблицу вендоров' .
                    '<br>name: ' . $data['name'];
                return false;
            }
            if ($result != false) {
                $vendor_id = mysqli_insert_id($this->link);
                return $vendor_id;
            }
        } else {
            return false;
        }
    }

    public function addAttributeGroup($data) {
        global $ERROR;
        if ($this->status AND $this->checkAttributeGroupData($data)) {
            $sql = 'INSERT INTO attribute_group (name, parent_id) VALUES ("' .
                $data['name'] . '", "' .
                (int)$data['parent_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления группы аттрибутов в таблицу нашей БД' .
                    '<br>name: ' . $data['name'] .
                    '<br>parent_id: ' . $data['parent_id'];
                return false;
            }
            if ($result != false) {
                $attribute_group_id = mysqli_insert_id($this->link);
                return $attribute_group_id;
            }
        } else {
            return false;
        }
    }

    public function addAttribute($data) {
        global $ERROR;
        if ($this->status AND $this->checkAttributeGroupData($data)) {
            $sql = 'INSERT INTO attribute (name, group_id) VALUES ("' .
                $data['name'] . '", "' .
                (int)$data['group_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления аттрибута в таблицу нашей БД' .
                    '<br>name: ' . $data['name'] .
                    '<br>group_id: ' . $data['group_id'];
                return false;
            }
            if ($result != false) {
                $attribute_id = mysqli_insert_id($this->link);
                return $attribute_id;
            }
        } else {
            return false;
        }
    }

    public function addAttributeValue($data) {
        global $ERROR;
        if ($this->status AND $this->checkAttributeValueData($data)) {
            $sql = 'INSERT INTO attribute_value (attribute_id, value) VALUES ("' .
                (int)$data['attribute_id'] . '", "' .
                $data['value'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления значения аттрибута в нашу таблицу' .
                    '<br>value: ' . $data['value'] .
                    '<br>attribute_id: ' . $data['attribute_id'];
                return false;
            }
            if ($result != false) {
                $attribute_value_id = mysqli_insert_id($this->link);
                return $attribute_value_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderCategory($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderCategoryData($data)) {
            $sql = 'INSERT INTO provider_category (provider_id, provider_category_id, name, provider_parent_id, provider_parent_cat_name) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['provider_category_id'] . '", "' .
                $data['provider_category_name'] . '", "' .
                $data['provider_category_parent_id'] . '", "' .
                $data['provider_parent_cat_name'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления категории в таблицу категорий поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>provider_category_id: ' . $data['provider_category_id'] .
                    '<br>provider_category_name: ' . $data['provider_category_name'] .
                    '<br>provider_category_parent_id: ' . $data['provider_category_parent_id'];
                return false;
            }
            if ($result != false) {
                $category_id = mysqli_insert_id($this->link);
                return $category_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderManufacturer($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderManufacturerData($data)) {
            $sql = 'INSERT INTO provider_manufacturer (provider_id, name, description, image) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления производителя в таблицу производителей поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>name: ' . $data['name'] .
                    '<br>description: ' . $data['description'] .
                    '<br>image: ' . $data['image'];
                return false;
            }
            if ($result != false) {
                $manufacturer_id = mysqli_insert_id($this->link);
                return $manufacturer_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderModel($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderModelData($data)) {
            $sql = 'INSERT INTO provider_model (provider_id, name) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления модели в таблицу моделей поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>name: ' . $data['name'];
                return false;
            }
            if ($result != false) {
                $model_id = mysqli_insert_id($this->link);
                return $model_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderVendor($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderVendorData($data)) {
            $sql = 'INSERT INTO provider_vendor (provider_id, name, description, image) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления вендора в таблицу вендоров поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>name: ' . $data['name'];
                return false;
            }
            if ($result != false) {
                $vendor_id = mysqli_insert_id($this->link);
                return $vendor_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderAttributeGroup($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderAttributeGroupData($data)) {
            $sql = 'INSERT INTO provider_attribute_group (provider_id, name, parent_id) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                (int)$data['parent_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления группы аттрибутов в таблицу поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>name: ' . $data['name'] .
                    '<br>parent_id: ' . $data['parent_id'];
                return false;
            }
            if ($result != false) {
                $attribute_group_id = mysqli_insert_id($this->link);
                return $attribute_group_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderAttribute($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderAttributeData($data)) {
            $sql = 'INSERT INTO provider_attribute (provider_id, name, group_id) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                (int)$data['group_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления аттрибута в таблицу поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>name: ' . $data['name'] .
                    '<br>group_id: ' . $data['group_id'];
                return false;
            }
            if ($result != false) {
                $attribute_id = mysqli_insert_id($this->link);
                return $attribute_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderAttributeValue($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderAttributeValueData($data)) {
            $sql = 'INSERT INTO provider_attribute_value (provider_id, attribute_id, value) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                (int)$data['attribute_id'] . '", "' .
                $data['value'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления значения аттрибута в таблицу поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>value: ' . $data['value'] .
                    '<br>attribute_id: ' . $data['attribute_id'];
                return false;
            }
            if ($result != false) {
                $attribute_value_id = mysqli_insert_id($this->link);
                return $attribute_value_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderAttributeProduct($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderAttributeProductData($data)) {
            $sql = 'INSERT INTO provider_attribute_product (product_id, attribute_value_id) VALUES (' .
                (int)$data['product_id'] . ', ' .
                (int)$data['attribute_value_id'] . ')';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления значения аттрибута для продукта' .
                    '<br>product_id: ' . $data['product_id'] .
                    '<br>attribute_value_id: ' . $data['attribute_value_id'];
                return false;
            }
            if ($result != false) {
                $attribute_product_id = mysqli_insert_id($this->link);
                return $attribute_product_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderProduct($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderProductData($data)) {
            $sql = 'INSERT INTO provider_product (provider_id, provider_product_id, name, description, category_id, model_id, vendor_id, manufacturer_id, width, height, length, weight, version, date_add) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                mysqli_real_escape_string($this->link, $data['provider_product_id']) . '", "' .
                mysqli_real_escape_string($this->link, $data['name']) . '", "' .
                mysqli_real_escape_string($this->link, $data['description']) . '", "' .
                (int)$data['category_id'] . '", "' .
                (int)$data['model_id'] . '", "' .
                (int)$data['vendor_id'] . '", "' .
                (int)$data['manufacturer_id'] . '", "' .
                (float)$data['width'] . '", "' .
                (float)$data['height'] . '", "' .
                (float)$data['length'] . '", "' .
                (float)$data['weight'] . '", "' .
                $data['version'] .
                '", NOW())';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления продукта в таблицу продуктов поставщиков' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>provider_product_id: ' . $data['provider_product_id'] .
                    '<br>name: ' . $data['name'] .
                    '<br>description: ' . $data['description'] .
                    '<br>category_id: ' . $data['category_id'] .
                    '<br>model_id: ' . $data['model_id'] .
                    '<br>vendor_id: ' . $data['vendor_id'] .
                    '<br>manufacturer_id: ' . $data['manufacturer_id'] .
                    '<br>width: ' . $data['width'] .
                    '<br>height: ' . $data['height'] .
                    '<br>length: ' . $data['length'] .
                    '<br>weight: ' . $data['weight'] .
                    '<br>version: ' . $data['version'];
                return false;
            }
            if ($result != false) {
                $product_id = mysqli_insert_id($this->link);
                return $product_id;
            }
        } else {
            $ERROR['Db'][] = 'Нет соединения или ошибка при проверке данных.' .
                '<br>provider_product_id: ' . $data['provider_product_id'] .
                '<br>name: ' . $data['name'];

            return false;
        }
    }

    public function addProviderProductImage($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderProductImageData($data)) {
            $sql = 'INSERT INTO provider_image (provider_id, product_id, image) VALUES (' .
                (int)$data['provider_id'] . ', ' .
                (int)$data['product_id'] . ', "' .
                mysqli_real_escape_string($this->link, $data['image']) . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления изображения для продукта поставщика' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>product_id: ' . $data['product_id'] .
                    '<br>image: ' . $data['image'];
                return false;
            }
            if ($result != false) {
                $image_product_id = mysqli_insert_id($this->link);
                return $image_product_id;
            }
        } else {
            return false;
        }
    }

    public function addProviderProductTotal($data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderProductTotalData($data)) {
            $sql = 'INSERT INTO provider_product_total (provider_id, product_id, total, price_usd, price_rub, transit, transit_date, date_add) VALUES (' .
                (int)$data['provider_id'] . ', ' .
                (int)$data['product_id'] . ', ' .
                (int)$data['total'] . ', ' .
                (float)$data['price_usd'] . ', ' .
                (float)$data['price_rub'] . ', ' .
                (int)$data['transit'] . ', ' .
                (int)$data['transit_date'] . ', ' .
                 NOW() . ')';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления provider_product_total для продукта поставщика' .
                    '<br>provider_id: ' . $data['provider_id'] .
                    '<br>product_id: ' . $data['product_id'] .
                    '<br>total: ' . $data['total'] .
                    '<br>price_usd: ' . $data['price_usd'] .
                    '<br>price_rub: ' . $data['price_rub'] .
                    '<br>transit: ' . $data['transit'] .
                    '<br>transit_date: ' . $data['transit_date'];
                return false;
            }
            if ($result != false) {
                $total_product_id = mysqli_insert_id($this->link);
                return $total_product_id;
            }
        } else {
            return false;
        }
    }

    public function addMap($code, $our_id, $provider_id) {
        global $ERROR;
        if ($this->status) {
            $sql = 'INSERT INTO map (code, our_id, provider_id) VALUES ("' .
                $code . '", "' .
                (int)$our_id . '", "' .
                (int)$provider_id . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления записи в таблицу сопоставления' .
                    '<br>code: ' . $code .
                    '<br>our_item_id: ' . $our_id .
                    '<br>our_provider_item_id: ' . $provider_id;
                return false;
            }
            if ($result != false) {
                $category_id = mysqli_insert_id($this->link);
                return $category_id;
            }
        } else {
            return false;
        }
    }

    public function editCategory($cat_id, $data) {
        global $ERROR;
        if ($this->status AND $this->checkCategoryData($data)) {
            $sql = 'UPDATE category SET name = "'. $data['name'] .
                '", description="' . $data['description'] .
                '", parent_id = "' . $data['parent_id'] .
                '", image = "' . $data['image'] .
                '" WHERE id = ' . (int)$cat_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка обновления записи в таблице' .
                    '<br>cat_id: ' . $cat_id;
                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function editProviderCategory($cat_id, $data) {
        global $ERROR;
        if ($this->status AND $this->checkProviderCategoryData($data)) {
            $sql = 'UPDATE provider_category SET provider_id = "'. $data['provider_id'] .
                '", provider_category_id = "' . $data['provider_category_id'] .
                '", name = "' . $data['provider_category_name'] .
                '", provider_parent_id = "' . $data['provider_category_parent_id'] .
                '", provider_parent_cat_name = "' . $data['provider_parent_cat_name'] .
                '" WHERE id = ' . (int)$cat_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка обновления записи в таблице категорий поставщиков' .
                    '<br>cat_id: ' . $cat_id;
                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    private function checkCategoryData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            }
        } else {
            return false;
        }
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = '';
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        return true;
    }

    private function checkManufacturerData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['image'])) {
            $data['image'] = '';
        }

        return true;
    }

    private function checkModelData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function checkVendorData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        return true;


    }

    private function checkAttributeGroupData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        if (!isset($data['parent_id'])) {
            $data['parent_id'] = '';
        }
        return true;


    }

    private function checkAttributeData(&$data) {
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        if (!isset($data['group_id'])) {
            $data['group_id'] = '';
        }
        return true;


    }

    private function checkAttributeValueData(&$data) {

        if (isset($data['attribute_id'])) {
            if ($data['attribute_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['value'])) {
            if ($data['value'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        return true;


    }

    private function checkProductData(&$data) {
        // Проверяем имя товара
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем описание товара
        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        // Проверяем ширину
        if (!isset($data['width'])) {
            $data['width'] = '';
        }

        // Проверяем высоту
        if (!isset($data['height'])) {
            $data['height'] = '';
        }

        // Проверяем длину (глубину)
        if (!isset($data['length'])) {
            $data['length'] = '';
        }

        // Проверяем вес
        if (!isset($data['weight'])) {
            $data['weight'] = '';
        }

        // Проверяем версию
        if (!isset($data['version'])) {
            $data['version'] = '';
        }

        // Проверяем id категории
        if (isset($data['category_id'])) {
            if ($data['category_id'] == '' OR $data['category_id'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем id модели (оригинальный номер)
        if (isset($data['model_id'])) {
            if ($data['model_id'] == '' OR $data['model_id'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем id вендора
        if (isset($data['vendor_id'])) {
            if ($data['vendor_id'] == '' OR $data['vendor_id'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем id производителя (бренда)
        if (isset($data['manufacturer_id'])) {
            if ($data['manufacturer_id'] == '' OR $data['manufacturer_id'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    private function checkProviderCategoryData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }
        if (!isset($data['provider_category_id'])) {
            $data['provider_category_id'] = '';
            if (!isset($data['provider_category_name'])) {
                return false;
            }
        } elseif (!isset($data['provider_category_name'])) {
            $data['provider_category_name'] = '';
        }
        if (!isset($data['provider_category_parent_id'])) {
            $data['provider_category_parent_id'] = '';
        }
        if (!isset($data['provider_parent_cat_name'])) {
            $data['provider_parent_cat_name'] = '';
        }

        return true;
    }

    private function checkProviderManufacturerData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        return true;
    }

    private function checkProviderModelData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function checkProviderVendorData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        return true;


    }

    private function checkProviderAttributeGroupData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        if (!isset($data['parent_id'])) {
            $data['parent_id'] = '';
        }
        return true;


    }

    private function checkProviderAttributeData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        if (!isset($data['group_id'])) {
            $data['group_id'] = '';
        }
        return true;


    }

    private function checkProviderAttributeValueData(&$data) {
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['attribute_id'])) {
            if ($data['attribute_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['value'])) {
            if ($data['value'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }

        return true;


    }

    private function checkProviderAttributeProductData(&$data) {
        if (isset($data['product_id'])) {
            if ($data['product_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['attribute_value_id'])) {
            if ($data['attribute_value_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    private function checkProviderProductData(&$data) {
        // Проверяем id поставщика
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем id товара поставщика
        if (isset($data['provider_product_id'])) {
            if ($data['provider_product_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем имя товара
        if (isset($data['name'])) {
            if ($data['name'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем описание товара
        if (!isset($data['description'])) {
            $data['description'] = '';
        }

        // Проверяем ширину
        if (!isset($data['width'])) {
            $data['width'] = '';
        }

        // Проверяем высоту
        if (!isset($data['height'])) {
            $data['height'] = '';
        }

        // Проверяем длину (глубину)
        if (!isset($data['length'])) {
            $data['length'] = '';
        }

        // Проверяем вес
        if (!isset($data['weight'])) {
            $data['weight'] = '';
        }

        // Проверяем версию
        if (!isset($data['version'])) {
            $data['version'] = '';
        }

        // Проверяем id категории
        if (isset($data['category_id'])) {
            if ($data['category_id'] == '' OR $data['category_id'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем id модели (оригинальный номер)
        if (!isset($data['model_id'])) {
            $data['model_id'] = '';
        }

        // Проверяем id вендора
        if (!isset($data['vendor_id'])) {
            $data['vendor_id'] = '';
        }

        // Проверяем id производителя (бренда)
        if (!isset($data['manufacturer_id'])) {
            $data['manufacturer_id'] = '';
        }

        return true;
    }

    private function checkProviderProductImageData(&$data) {
        if (isset($data['product_id'])) {
            if ($data['product_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        if (isset($data['image'])) {
            if ($data['image'] == '') {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    private function checkProviderProductTotalData(&$data) {
        // Проверяем id товара
        if (isset($data['product_id'])) {
            if ($data['product_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем provider_id товара
        if (isset($data['provider_id'])) {
            if ($data['provider_id'] == '') {
                return false;
            }
        } else {
            return false;
        }

        // Проверяем total товара
        if (!isset($data['total'])) {
            $data['total'] = 0;
        }

        // Проверяем price_usd товара
        if (!isset($data['price_usd'])) {
            $data['price_usd'] = 0;
        }

        // Проверяем price_rub товара
        if (!isset($data['price_rub'])) {
            $data['price_rub'] = 0;
        }

        // Проверяем transit товара
        if (!isset($data['transit'])) {
            $data['transit'] = 0;
        }

        // Проверяем transit товара
        if (!isset($data['transit_date'])) {
            $data['transit_date'] = null;
        }

        return true;
    }
}

?>