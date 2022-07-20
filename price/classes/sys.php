<?php
class Sys {
    public function addLog($code, $module, $message) {
        //$path = $_SERVER['DOCUMENT_ROOT'] . '/price/log/logs.log';
        $path = $_SERVER['DOCUMENT_ROOT'] . '/log/logs.log';
        if (file_exists($path)) {
            $f = fopen($path, "a");
            fputs($f, date('d.m.Y-H:i:s') . ' ' . $code . '  ' . $module .  '  ' . $message . "\r\n");
            fclose($f);
        } else {
            $f = fopen($path, "w");
            fputs($f, '  DATE     CODE  MODULE        MESSAGE' . "\r\n");
            fputs($f, date('d.m.Y-H:i:s') . ' ' . $code . '  ' . $module .  '  ' . $message . "\r\n");
            fclose($f);
        }
    }

    public function execInBackground($cmd, $from = 0, $to = 0) {
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen("start /B ". $cmd . " " . $from . " " . $to, "r"));
            //$temp = popen("start /B ". $cmd, "r");
            //echo 'Think';
        }
        else {
            exec($cmd . " > /dev/null &");
        }
    }
}