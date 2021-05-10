<?php
//$fNew = fopen('log/new_' . date("mdY") . '.log', 'w');
//fputs($fNew, "В базу были добавлены новые товары: \r\n");
//fclose($fNew);

//Получаем артиклу и цены из ИМ в массив
$imVttPriceNotNull = array();
$fImPrice = fopen('https://kplus79.ru/image/csv_export_vtt_not_null.csv','r');
$i = 0;
while (($data = fgetcsv($fImPrice, 0, ';', '"')) !== FALSE) {
    $imVttPriceNotNull[$i][0] = $data[0];
    $imVttPriceNotNull[$i][1] = $data[1];
    $i++;
}
fclose($fImPrice);
?>
