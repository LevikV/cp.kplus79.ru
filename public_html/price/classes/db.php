<?php

class Db {
    public $status;

    function __construct()
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/public_html/price/system/config.php');
        mysqli_report(MYSQLI_REPORT_ALL);

        try {
            $link = mysqli_connect($db_server, $db_user, $db_pswd, $db_name);
            if ($link != false) {
                $this->status = true;
            }
        } catch (Exception $e) {
            $this->status = false;
        }




    }

    public function getThink () {
        echo 'ThinkDo';
    }
}

?>