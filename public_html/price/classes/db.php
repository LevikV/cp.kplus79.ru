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

    public function getOurCatIdByProvCatName($prov_cat_name, $prov_id) {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT our_category_id FROM category_map WHERE provider_category_id IN (SELECT id FROM provider_category WHERE name = "' . $prov_cat_name . '" AND provider_id = ' . (int)$prov_id .')';
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

}

?>