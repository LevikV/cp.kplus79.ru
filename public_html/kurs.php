<?php
$url = "https://www.cbr.ru/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//для возврата результата в виде строки, вместо прямого вывода в браузер
$returned = curl_exec($ch);
curl_close ($ch);
//
if ($returned == false) {
    $kurs = 77;
} else {
    $temp = substr($returned, strpos($returned, "_dollar"), 100);
    $temp = substr($temp,75,7);
    $temp[2] = '.';
    $kurs = ceil($temp);
}

echo $kurs;
echo "<br>";
echo "Текущее дата и время на сервере: ";
echo date('d-m-Y H:i:s');
?>