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

    public function getOurCatIdByProvCatId($prov_cat_id) {
        global $ERROR;
        if ($this->status) {
            $sql = 'SELECT our_category_id FROM category_map WHERE provider_category_id = (SELECT id FROM provider_category WHERE provider_category_id = "' . $prov_cat_id . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка поиска категории для сопоставления.' .
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
            $sql = 'INSERT INTO provider_category (provider_id, provider_category_id, provider_category_name, provider_category_parent_id) VALUES ("' .
                $data['provider_id'] . '", "' .
                $data['provider_category_id'] . '", "' .
                $data['provider_category_name'] . '", "' .
                $data['provider_category_parent_id'] . '")';
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

    public function addCategoryMap($our_cat_id, $our_provider_cat_id) {
        global $ERROR;
        if ($this->status) {
            $sql = 'INSERT INTO category_map (our_category_id, provider_category_id) VALUES ("' .
                (int)$our_cat_id . '", "' .
                (int)$our_provider_cat_id . '")';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления записи в таблицу сопоставления категорий' .
                    '<br>our_cat_id: ' . $our_cat_id .
                    '<br>our_provider_cat_id: ' . $our_provider_cat_id;
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
        return true;
    }

}

?>