<?php

class Db {
    public $status;
    private $link;

    function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ALL);

        try {
            $this->link = mysqli_connect(DB_SERVER, DB_USER, DB_PSWD, DB_NAME);
            if ($this->link != false) {
                $this->status = true;
            }
        } catch (Exception $e) {
            $this->status = false;
        }
    }

    public function addProduct($data) {
        if ($this->status) {

        }
    }

    public function addCategory($data) {
        global $ERROR;
        if ($this->status AND $this->checkCategoryData($data)) {
            $sql = 'INSERT INTO category (name, description, parent_id, image) VALUES (' .
                $data['name'] . ', ' .
                $data['description'] . ', ' .
                $data['parent_id'] . ', ' .
                $data['image'] . ')';
            try {
                $result = mysqli_query($this->link, $sql);
            } catch (Exception $e) {
                $ERROR['Db'][] = 'Ошибка добавления категории в БД при выполнении запроса к БД';
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
            if ($data['name'] != '') {
                return true;
            } else {
                return false;
            }
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
    }

    public function getThink () {
        echo 'ThinkDo';
    }
}

?>