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

    public function getThink () {
        echo 'ThinkDo';
    }
}

?>