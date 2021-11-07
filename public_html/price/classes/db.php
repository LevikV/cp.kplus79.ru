<?php

class Db extends Sys {
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

    private function connectDB() {
        //mysqli_report(MYSQLI_REPORT_ALL);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        global $ERROR;
        try {
            $this->link = mysqli_connect(DB_SERVER, DB_USER, DB_PSWD, DB_NAME);
            if ($this->link != false) {
                $this->status = true;
                return true;
            }
        } catch (Exception $e) {
            $ERROR['Db'][] = 'Ошибка создания подключения к БД';
            $this->status = false;
            return false;
        }
    }

    private function closeDB() {

        if ($this->status) {
            $result = mysqli_close($this->link);
            $this->status = false;
        }

    }

    public function getCategories() {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM category ';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения всех наших категорий из таблицы category' . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
                if (empty($rows))
                    return null;
                else
                    return $rows;
            }
        } else {
            return false;
        }
    }

    public function getProviderProductCount($prov_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT COUNT(*) FROM provider_product WHERE provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения количества товаров поставщика из нашей БД по prov_id' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM attribute WHERE name = "' . $attribute_name .
                '" AND group_id = (SELECT id FROM attribute_group WHERE name = "' . $attribute_group_name . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска аттрибута по имени в нашей базе.' . "\r\n";
                $message .= 'attribute_name: ' . $attribute_name . "\r\n";
                $message .= 'attribute_group_name: ' . $attribute_group_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getOurItemIdByProvItemId($code, $prov_item_id, $prov_id) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            switch ($code) {
                case 'category': {
                    $sql = 'SELECT our_id FROM map WHERE code = "'. $code . '" AND provider_id = (SELECT id FROM provider_category WHERE provider_category_id = "' . $prov_item_id . '" AND provider_id = ' . (int)$prov_id .')';
                }
            }

            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска сущности для сопоставления.' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'code: ' . $code . "\r\n";
                $message .= 'сущность_id: ' . $prov_item_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getCatIdByProvCatName($prov_id, $prov_cat_name, $prov_root_cat_name) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_category WHERE name = "' . $prov_cat_name .
                '" AND provider_id = '. (int)$prov_id . ' AND provider_parent_cat_name = "' . $prov_root_cat_name . '"';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id категории по имени' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_cat_name: ' . $prov_cat_name . "\r\n";
                $message .= 'prov_root_cat_name: ' . $prov_root_cat_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getModelIdByProvModelName($prov_id, $prov_model_name) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_model WHERE name = "' . $prov_model_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id модели по имени модели поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_model_name: ' . $prov_model_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();

                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getVendorIdByProvVendorName($prov_id, $prov_vendor_name) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_vendor WHERE name = "' . $prov_vendor_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id вендора по имени вендора поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_vendor_name: ' . $prov_vendor_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getManufIdByProvManufName($prov_id, $prov_manuf_name) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_manufacturer WHERE name = "' . $prov_manuf_name .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id производителя (брэнда) по имени брэнда поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_manuf_name: ' . $prov_manuf_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getProvCatByProvCatId($prov_id, $prov_cat_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM provider_category WHERE provider_category_id = "' . $prov_cat_id .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения категории поставщика по provCatId' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_cat_id: ' . $prov_cat_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                //$row = $result->fetch_row();
                //$row = mysqli_fetch_array($result);
                $row = mysqli_fetch_all($result, MYSQLI_ASSOC);
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getCatIdByProvCatId($prov_id, $prov_cat_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_category WHERE provider_category_id = "' . $prov_cat_id .
                '" AND provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id категории по id категории поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_cat_id: ' . $prov_cat_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getOurProviderAttributeIdByName($prov_id, $attribute_name, $attribute_group_name) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_attribute WHERE provider_id = ' . (int)$prov_id . ' AND name = "' . $attribute_name .
                '" AND group_id = (SELECT id FROM provider_attribute_group WHERE provider_id = ' . (int)$prov_id . ' AND name = "' . $attribute_group_name . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска аттрибута поставщика по имени.' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'attribute_name: ' . $attribute_name . "\r\n";
                $message .= 'attribute_group_name: ' . $attribute_group_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getOurProviderAttributeValueIdByValue($prov_id, $attrib_id, $value) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_attribute_value WHERE provider_id = ' . (int)$prov_id . ' AND attribute_id = ' . (int)$attrib_id .
                ' AND value = "' . $value . '"';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id значения аттрибута поставщика по id аттрибута и его значению' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'attribute_id: ' . $attrib_id . "\r\n";
                $message .= 'attribute_value: ' . $value . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getProviderProductAttributeValueByAttribName($prov_id, $product_id, $attrib_name, $attrib_group_name) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
//            $sql = 'SELECT id FROM provider_attribute WHERE name = "'. $attrib_name .'" AND provider_id = '. $prov_id .
//                ' AND group_id = (SELECT id FROM provider_attribute_group WHERE name = "' . $attrib_group_name . '" AND provider_id = '. $prov_id . ')';
//
//            $sql2 = 'SELECT id FROM provider_attribute_value WHERE provider_id = ' . $prov_id .
//                ' AND attribute_id = (SELECT id FROM provider_attribute WHERE name = "'. $attrib_name .'" AND provider_id = '. $prov_id .
//                ' AND group_id = (SELECT id FROM provider_attribute_group WHERE name = "' . $attrib_group_name . '" AND provider_id = '. $prov_id . '))';
//
//            $sql3 = 'SELECT attribute_value_id FROM provider_attribute_product WHERE product_id = '. $product_id .
//                ' AND attribute_value_id IN (SELECT id FROM provider_attribute_value WHERE provider_id = ' . $prov_id .
//                ' AND attribute_id = (SELECT id FROM provider_attribute WHERE name = "'. $attrib_name .'" AND provider_id = '. $prov_id .
//                ' AND group_id = (SELECT id FROM provider_attribute_group WHERE name = "' . $attrib_group_name . '" AND provider_id = '. $prov_id . ')))';
//
            $sql = 'SELECT value FROM provider_attribute_value WHERE id = (SELECT attribute_value_id FROM provider_attribute_product WHERE product_id = '. $product_id .
                ' AND attribute_value_id IN (SELECT id FROM provider_attribute_value WHERE provider_id = ' . $prov_id .
                ' AND attribute_id = (SELECT id FROM provider_attribute WHERE name = "'. $attrib_name .'" AND provider_id = '. $prov_id .
                ' AND group_id = (SELECT id FROM provider_attribute_group WHERE name = "' . $attrib_group_name . '" AND provider_id = '. $prov_id . '))))';

            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска значения аттрибута по имени аттрибута поставщика и id продукта' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $message .= 'attrib_name: ' . $attrib_name . "\r\n";
                $message .= 'attrib_group_name: ' . $attrib_group_name . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getOurProviderProductIdByProviderProductId($prov_id, $prov_product_id) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT id FROM provider_product WHERE provider_id = ' . (int)$prov_id . ' AND provider_product_id = "' . $prov_product_id . '"';
            try {
                $result = array();
-               $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска товара поставщика в таблице поставщиков по provider_id и provider_product_id' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'prov_product_id: ' . $prov_product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result->num_rows > 0) {
                $row = $result->fetch_row();
                if (empty($row))
                    return null;
                else
                    return $row[0];
            }
        } else {
            return false;
        }
    }

    public function getProviderProductTotal($prov_id, $product_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM provider_product_total WHERE provider_id = '. (int)$prov_id . ' AND product_id = ' . (int)$product_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения данных total продукта поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $rows = array();
                while($row = $result->fetch_array()){
                    $rows[] = array(
                        'id' => $row["id"],
                        'provider_id' => $row["provider_id"],
                        'product_id' => $row["product_id"],
                        'total' => $row["total"],
                        'price_usd' => $row["price_usd"],
                        'price_rub' => $row["price_rub"],
                        'transit' => $row["transit"],
                        'transit_date' => $row["transit_date"],
                        'date_add' => $row["date_add"],
                        'date_edit' => $row["date_edit"],
                        'date_update' => $row["date_update"]
                    );
                }
                if (empty($rows))
                    return null;
                else
                    return $rows[0];
            }
        } else {
            return false;
        }
    }

    public function getProviderProduct($prov_id, $product_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM provider_product WHERE provider_id = '. (int)$prov_id . ' AND id = ' . (int)$product_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения продукта поставщика из provider_products' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);
                // выходим из функции
                return false;
            }
            if ($result != false) {
                $rows = array();
                while($row = $result->fetch_array()){
                    $rows[] = array(
                        'id' => $row["id"],
                        'provider_id' => $row["provider_id"],
                        'provider_product_id' => $row["provider_product_id"],
                        'name' => $row["name"],
                        'description' => $row["description"],
                        'category_id' => $row["category_id"],
                        'model_id' => $row["model_id"],
                        'vendor_id' => $row["vendor_id"],
                        'manufacturer_id' => $row["manufacturer_id"],
                        'width' => $row["width"],
                        'height' => $row["height"],
                        'length' => $row["length"],
                        'weight' => $row["weight"],
                        'version' => $row["version"],
                        'status' => $row["status"],
                        'date_add' => $row["date_add"],
                        'date_edit' => $row["date_edit"],
                        'date_update' => $row["date_update"]
                    );
                }
                if (empty($rows))
                    return null;
                else
                    return $rows[0];
            }
        } else {
            return false;
        }
    }

    public function getProviderProducts($prov_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM provider_product WHERE provider_id = '. (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения всех товаров поставщика' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                $rows = array();
                while($row = $result->fetch_array()){
                    $rows[] = array(
                        'id' => $row["id"],
                        'provider_id' => $row["provider_id"],
                        'provider_product_id' => $row["provider_product_id"],
                        'name' => $row["name"],
                        'description' => $row["description"],
                        'category_id' => $row["category_id"],
                        'model_id' => $row["model_id"],
                        'vendor_id' => $row["vendor_id"],
                        'manufacturer_id	' => $row["manufacturer_id	"],
                        'width' => $row["width"],
                        'height' => $row["height"],
                        'length' => $row["length"],
                        'weight' => $row["weight"],
                        'version' => $row["version"],
                        'status' => $row["status"],
                        'date_add' => $row["date_add"],
                        'date_edit' => $row["date_edit"],
                        'date_update' => $row["date_update"]
                    );
                }
                if (empty($rows))
                    return null;
                else
                    return $rows;
            }
        } else {
            return false;
        }
    }

    public function getProviderProductImages($prov_id, $product_id) {
        //
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'SELECT * FROM provider_image WHERE provider_id = '. (int)$prov_id . ' AND product_id = ' . (int)$product_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка получения изображения продукта поставщика из provider_image' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);
                // выходим из функции
                return false;
            }
            if ($result != false) {
                $rows = array();
                while($row = $result->fetch_array()){
                    $rows[] = array(
                        'id' => $row["id"],
                        'provider_id' => $row["provider_id"],
                        'product_id' => $row["product_id"],
                        'image' => $row["image"],
                        'main' => $row["main"]
                    );
                }
                if (empty($rows))
                    return null;
                else
                    return $rows;
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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkCategoryData($data)) {
            $sql = 'INSERT INTO category (name, description, parent_id, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['parent_id'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления категории в нашу базу категорий' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'description: ' . $data['description'] . "\r\n";
                $message .= 'parent_id: ' . $data['parent_id'] . "\r\n";
                $message .= 'image: ' . $data['image'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkManufacturerData($data)) {
            $sql = 'INSERT INTO manufacturer (name, description, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления производителя в нашу таблицу производителей' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'description: ' . $data['description'] . "\r\n";
                $message .= 'image: ' . $data['image'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkModelData($data)) {
            $sql = 'INSERT INTO model (name) VALUES ("' .
                $data['name'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления модели в нашу таблицу моделей' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkVendorData($data)) {
            $sql = 'INSERT INTO vendor (name, description, image) VALUES ("' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления вендора в vendor' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkAttributeGroupData($data)) {
            $sql = 'INSERT INTO attribute_group (name, parent_id) VALUES ("' .
                $data['name'] . '", "' .
                (int)$data['parent_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления группы атрибутов в attribute_group' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'parent_id: ' . $data['parent_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkAttributeGroupData($data)) {
            $sql = 'INSERT INTO attribute (name, group_id) VALUES ("' .
                $data['name'] . '", "' .
                (int)$data['group_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления аттрибута в таблицу нашей БД' . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'group_id: ' . $data['group_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkAttributeValueData($data)) {
            $sql = 'INSERT INTO attribute_value (attribute_id, value) VALUES ("' .
                (int)$data['attribute_id'] . '", "' .
                $data['value'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления значения аттрибута в нашу таблицу' . "\r\n";
                $message .= 'value: ' . $data['value'] . "\r\n";
                $message .= 'attribute_id: ' . $data['attribute_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
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
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления категории в таблицу категорий поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'provider_category_id: ' . $data['provider_category_id'] . "\r\n";
                $message .= 'provider_category_name: ' . $data['provider_category_name'] . "\r\n";
                $message .= 'provider_category_parent_id: ' . $data['provider_category_parent_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderManufacturerData($data)) {
            $sql = 'INSERT INTO provider_manufacturer (provider_id, name, description, image) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления производителя в таблицу производителей поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'description: ' . $data['description'] . "\r\n";
                $message .= 'image: ' . $data['image'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderModelData($data)) {
            $sql = 'INSERT INTO provider_model (provider_id, name) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления модели в таблицу моделей поставщиков в provider_model' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderVendorData($data)) {
            $sql = 'INSERT INTO provider_vendor (provider_id, name, description, image) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                $data['description'] . '", "' .
                $data['image'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления вендора в таблицу вендоров поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderAttributeGroupData($data)) {
            $sql = 'INSERT INTO provider_attribute_group (provider_id, name, parent_id) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                (int)$data['parent_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления группы аттрибутов в таблицу поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'parent_id: ' . $data['parent_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderAttributeData($data)) {
            $sql = 'INSERT INTO provider_attribute (provider_id, name, group_id) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                $data['name'] . '", "' .
                (int)$data['group_id'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления аттрибута в таблицу поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'group_id: ' . $data['group_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderAttributeValueData($data)) {
            $sql = 'INSERT INTO provider_attribute_value (provider_id, attribute_id, value) VALUES ("' .
                (int)$data['provider_id'] . '", "' .
                (int)$data['attribute_id'] . '", "' .
                $data['value'] . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления значения аттрибута в таблицу поставщиков' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'value: ' . $data['value'] . "\r\n";
                $message .= 'attribute_id: ' . $data['attribute_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderAttributeProductData($data)) {
            $sql = 'INSERT INTO provider_attribute_product (product_id, attribute_value_id) VALUES (' .
                (int)$data['product_id'] . ', ' .
                (int)$data['attribute_value_id'] . ')';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления значения аттрибута для продукта' . "\r\n";
                $message .= 'product_id: ' . $data['product_id'] . "\r\n";
                $message .= 'attribute_value_id: ' . $data['attribute_value_id'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductData($data)) {
            $sql = 'INSERT INTO provider_product (provider_id, provider_product_id, name, description, category_id, model_id, vendor_id, manufacturer_id, width, height, length, weight, version, status, date_add) VALUES ("' .
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
                $data['version'] . '", 1, NOW())';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id категории по имени' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'prov_cat_name: ' . $data['provider_product_id'] . "\r\n";
                $message .= 'prov_root_cat_name: ' . $data['name'] . "\r\n";
                $message .= 'description: ' . $data['description'] . "\r\n";
                $message .= 'category_id: ' . $data['category_id'] . "\r\n";
                $message .= 'model_id: ' . $data['model_id'] . "\r\n";
                $message .= 'vendor_id: ' . $data['vendor_id'] . "\r\n";
                $message .= 'manufacturer_id: ' . $data['manufacturer_id'] . "\r\n";
                $message .= 'width: ' . $data['width'] . "\r\n";
                $message .= 'height: ' . $data['height'] . "\r\n";
                $message .= 'length: ' . $data['length'] . "\r\n";
                $message .= 'weight: ' . $data['weight'] . "\r\n";
                $message .= 'version: ' . $data['version'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductImageData($data)) {
            $sql = 'INSERT INTO provider_image (provider_id, product_id, image) VALUES (' .
                (int)$data['provider_id'] . ', ' .
                (int)$data['product_id'] . ', "' .
                mysqli_real_escape_string($this->link, $data['image']) . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $message = 'Ошибка добавления изображения для продукта поставщика' . "\r\n";
                $message .= 'provider_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'product_id: ' . $data['product_id'] . "\r\n";
                $message .= 'image: ' . $data['image'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductTotalData($data)) {
            if ($data['transit_date'] != 'null') $data['transit_date'] = '"' . $data['transit_date'] . '"';
            $sql = 'INSERT INTO provider_product_total (provider_id, product_id, total, price_usd, price_rub, transit, transit_date, date_add) VALUES (' .
                (int)$data['provider_id'] . ', ' .
                (int)$data['product_id'] . ', ' .
                (int)$data['total'] . ', ' .
                (float)$data['price_usd'] . ', ' .
                (float)$data['price_rub'] . ', ' .
                (int)$data['transit'] . ', ' .
                $data['transit_date'] . ',' .
                ' NOW())';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления provider_product_total для продукта поставщика' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'prov_cat_name: ' . $data['product_id'] . "\r\n";
                $message .= 'total: ' . $data['total'] . "\r\n";
                $message .= 'price_usd: ' . $data['price_usd'] . "\r\n";
                $message .= 'price_rub: ' . $data['price_rub'] . "\r\n";
                $message .= 'transit: ' . $data['transit'] . "\r\n";
                $message .= 'transit_date: ' . $data['transit_date'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'INSERT INTO map (code, our_id, provider_id) VALUES ("' .
                $code . '", "' .
                (int)$our_id . '", "' .
                (int)$provider_id . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка добавления provider_product_total для продукта поставщика' . "\r\n";
                $message .= 'code: ' . $code . "\r\n";
                $message .= 'our_item_id: ' . $our_id . "\r\n";
                $message .= 'our_provider_item_id: ' . $provider_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkCategoryData($data)) {
            $sql = 'UPDATE category SET name = "'. $data['name'] .
                '", description="' . $data['description'] .
                '", parent_id = "' . $data['parent_id'] .
                '", image = "' . $data['image'] .
                '" WHERE id = ' . (int)$cat_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка обновления записи в таблице category' . "\r\n";
                $message .= 'cat_id: ' . $cat_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

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
        if (!mysqli_ping($this->link)) $this->connectDB();
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
                // Записываем в лог данные об ошибке
                $message = 'Ошибка обновления записи в таблице категорий поставщиков' . "\r\n";
                $message .= 'cat_id: ' . $cat_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function editProviderProductTotal($product_id, $data) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductTotalData($data)) {
            if ($data['transit_date'] != 'null') $data['transit_date'] = '"' . $data['transit_date'] . '"';
            $sql = 'UPDATE provider_product_total SET provider_id = '. (int)$data['provider_id'] .
                ', product_id = ' . (int)$data['product_id'] .
                ', total = ' . (int)$data['total'] .
                ', price_usd = ' . (float)$data['price_usd'] .
                ', price_rub = ' . (float)$data['price_rub'] .
                ', transit = ' . (int)$data['transit'] .
                ', transit_date = ' . $data['transit_date'] .
                ', date_edit = NOW()' .
                ', date_update = NOW()' .
                ' WHERE product_id = ' . (int)$product_id . ' AND provider_id = ' . (int)$data['provider_id'];
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка изменения записи в таблице provider_product_total' . "\r\n";
                $message .= 'prov_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'prov_cat_name: ' . $data['product_id'] . "\r\n";
                $message .= 'total: ' . $data['total'] . "\r\n";
                $message .= 'price_usd: ' . $data['price_usd'] . "\r\n";
                $message .= 'price_rub: ' . $data['price_rub'] . "\r\n";
                $message .= 'transit: ' . $data['transit'] . "\r\n";
                $message .= 'transit_date: ' . $data['transit_date'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function editProviderProduct($product_id, $data) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductData($data)) {
            $data['provider_product_id'] = mysqli_real_escape_string($this->link, $data['provider_product_id']);
            $data['name'] = mysqli_real_escape_string($this->link, $data['name']);
            $data['description'] = mysqli_real_escape_string($this->link, $data['description']);
            $sql = 'UPDATE provider_product SET provider_id = '. (int)$data['provider_id'] .
                ', provider_product_id = "' . $data['provider_product_id'] .
                '", name = "' . $data['name'] .
                '", description = "' . $data['description'] .
                '", category_id = ' . (int)$data['category_id'] .
                ', model_id = ' . (int)$data['model_id'] .
                ', vendor_id = ' . (int)$data['vendor_id'] .
                ', manufacturer_id = ' . (int)$data['manufacturer_id'] .
                ', width = ' . (float)$data['width'] .
                ', height = ' . (float)$data['height'] .
                ', length = ' . (float)$data['length'] .
                ', weight = ' . (float)$data['weight'] .
                ', version = "' . $data['version'] .
                '", status = ' . (int)$data['status'] .
                ', date_edit = NOW()' .
                ', date_update = NOW()' .
                ' WHERE id = ' . (int)$product_id . ' AND provider_id = ' . (int)$data['provider_id'];
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка изменения товара в таблице provider_product' . "\r\n";
                $message .= 'provider_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $message .= 'name: ' . $data['name'] . "\r\n";
                $message .= 'description: ' . $data['description'] . "\r\n";
                $message .= 'category_id: ' . $data['category_id'] . "\r\n";
                $message .= 'model_id: ' . $data['model_id'] . "\r\n";
                $message .= 'vendor_id: ' . $data['vendor_id'] . "\r\n";
                $message .= 'manufacturer_id: ' . $data['manufacturer_id'] . "\r\n";
                $message .= 'width: ' . $data['width'] . "\r\n";
                $message .= 'height: ' . $data['height'] . "\r\n";
                $message .= 'length: ' . $data['length'] . "\r\n";
                $message .= 'weight: ' . $data['weight'] . "\r\n";
                $message .= 'version: ' . $data['version'] . "\r\n";
                $message .= 'status: ' . $data['status'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function editProviderProductImage($prod_image_id, $data) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status AND $this->checkProviderProductData($data)) {
            $sql = 'UPDATE provider_image SET provider_id = '. (int)$data['provider_id'] .
                ', product_id = ' . (int)$data['product_id'] .
                ', image = "' . $data['image'] .
                '", main = ' . (int)$data['main'] .
                ' WHERE id = ' . $prod_image_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка изменения изображения товара в таблице provider_image' . "\r\n";
                $message .= 'provider_id: ' . $data['provider_id'] . "\r\n";
                $message .= 'product_id: ' . $data['product_id'] . "\r\n";
                $message .= 'image: ' . $data['image'] . "\r\n";
                $message .= 'main: ' . $data['main'] . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function editProviderProductAttributeValueByAttribName($prov_id, $product_id, $data) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            // Получаем id значения attribute_value
            //
            $attrib_id = $this->getOurProviderAttributeIdByName($prov_id, $data['attribute_name'], $data['attribute_group_name']);
            $attrib_value_id = $this->getOurProviderAttributeValueIdByValue($prov_id, $attrib_id, $data['attribute_value']);
            // если значения аттрибута Цвет нет в нашей базе в provider_attribute_value
            // то добавляем новое значение
            if ($attrib_value_id == null) {
                $data_val = array();
                $data_val['provider_id'] = $prov_id;
                $data_val['attribute_id'] = $attrib_id;
                $data_val['value'] = $data['attribute_value'];
                //
                $attrib_value_id = $this->addProviderAttributeValue($data_val);
            }
            $sql = 'UPDATE provider_attribute_product SET attribute_value_id = '. (int)$attrib_value_id .
                ' WHERE product_id = '. (int)$product_id .
                ' AND  attribute_value_id IN (SELECT id FROM provider_attribute_value WHERE provider_id = '. (int)$prov_id .
                ' AND attribute_id = ' . (int)$attrib_id .')';

            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка изменения аттрибута цвет в таблице provider_attribute_product' . "\r\n";
                $message .= 'provider_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function updateProviderProductTotal($prov_id, $product_id) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'UPDATE provider_product_total SET date_update = NOW()' .
                ' WHERE product_id = ' . (int)$product_id . ' AND provider_id = ' . (int)$prov_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка поиска id категории по имени' . "\r\n";
                $message .= 'prov_id: ' . $prov_id . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function updateProviderProduct($product_id) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'UPDATE provider_product SET date_update = NOW()' .
                ' WHERE id = ' . (int)$product_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка обновления записи в таблице provider_product' . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";

                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function setStatusProviderProduct($product_id, $status) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'UPDATE provider_product SET status = ' . (int)$status .
                ', date_update = NOW() WHERE id = ' . (int)$product_id;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $message = 'Ошибка установки статуса товару в таблице provider_product' . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $message .= 'status: ' . $status . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function checkProviderProductId($prov_id) {
        // Функция проверки на дубли id_provider_product товаров поставщика

        $provider_products = $this->getProviderProducts($prov_id);
        $products_id = array();
        $products_duplicate_id = array();
        foreach ($provider_products as $product) {
            if ($product['provider_product_id'] == '') echo 'У товара отсутствует id поставщика. Имя товара: ' . $product['name'];
            elseif (in_array($product['provider_product_id'], $products_id)) {
                echo 'Дублируется id товара поставщика. provider_product_id: ' . $product['provider_product_id'];
                $products_duplicate_id[] = $product['provider_product_id'];
            }
            else $products_id[] = $product['provider_product_id'];
        }
        echo 'Произведена проверка ' . count($provider_products) . ' товаров <br>';
        echo 'Дублей id ' . count($products_duplicate_id) . ' товаров <br>';
        echo 'Уникальных id ' . count($products_id) . ' товаров <br>';
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

        // Проверяем статус товар
        if (!isset($data['status'])) {
            $data['status'] = 0;
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
            $data['transit_date'] = "null";
        } elseif ($data['transit_date'] == '') $data['transit_date'] = "null";

        return true;
    }

    public function deleteProviderProductImage($id_prov_product_image) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'DELETE FROM provider_image WHERE id = ' . $id_prov_product_image;
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка удаления изображения товара в таблице provider_image' . "\r\n";
                $message .= 'id_prov_product_image: ' . $id_prov_product_image . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function deleteProviderAttributeProductByIdAttrib($product_id, $attrib_id) {
        global $ERROR;
        if (!mysqli_ping($this->link)) $this->connectDB();
        if ($this->status) {
            $sql = 'DELETE FROM provider_attribute_product WHERE product_id = ' . $product_id .
            ' AND attrib_value_id IN (SELECT id FROM provider_attribute_value WHERE attribute_id = '. $attrib_id . ')';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                // Записываем в лог данные об ошибке
                $message = 'Ошибка удаления аттрибута товара в таблице provider_attribute_product' . "\r\n";
                $message .= 'product_id: ' . $product_id . "\r\n";
                $message .= 'attrib_id: ' . $attrib_id . "\r\n";
                $this->addLog('ERROR', 'DB', $message);

                return false;
            }
            if ($result != false) {
                return true;
            }
        } else {
            return false;
        }
    }
}

?>