<?php
class Sys {
    public function addLog($code, $module, $message) {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public_html/price/log/logs.log';
        if (file_exists($path)) {
            $f = fopen($path, "a");
            fputs($f, date('mdY: ') . '    ' . $code . '    ' . $module .  '    ' . $message . "\r\n");
            fclose($f);
        } else {
            $f = fopen($path, "w");
            fputs($f, '    DATE      CODE    MODULE            MESSAGE' . "\r\n");
            fputs($f, date('mdY: ') . '    ' . $code . '    ' . $module .  '    ' . $message . "\r\n");
            fclose($f);
        }
    }
}