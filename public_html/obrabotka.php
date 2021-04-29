<?php
$kurs = 77;
$artikulOld = array();
$fOld = fopen('vtt_price_im.csv', 'r');
while ($data = fgetcsv($fOld, 1000, ';', '"') != FALSE) {
    $artikulOld[] = $data[0];
}
fclose($fOld);
//
$fVtt = fopen('vtt_price_all_new.csv','r');
$fIm = fopen('vtt_price_im.csv', 'w');
$fNew = fopen('new_goods.txt', 'w');
//Записываем строку заголовков
$caption = array("Artikul", "Name", "Manafacture", "Description", "Group0", "Group1", "Group2", "Quantity", "Price", "Width",
    "Height", "Depth", "Weight", "PhotoUrl", "PartNumber", "Vendor", "Compatibility", "ColorName");
fputcsv($fIm, $caption, ';', '"');
while ($data = fgetcsv($fVtt, 1000, ';', '"') != FALSE) {
    //Накрутка цены, в зависимости оригинал или нет
    if ((stripos($data[1], '(o)')===false) AND (stripos($data[1], '( o )')===false) AND (stripos($data[1], '(0)')===false) AND (stripos($data[1], '( 0 )')===false)) {
        $price = $data[8];
        $price = $price*$kurs+$price*$kurs*0.4;
        if ($price === 0) $price = '';
        $data[8] = $price;
    } else {
        $price = $data[8];
        $price = $price*$kurs+$price*$kurs*0.1;
        if ($price === 0) $price = '';
        $data[8] = $price;
    }
    //Записываем прайс лист для ИМ
    fputcsv($fIm, $data, ';', '"');
    //Проверяем новый это товар или уже есть в базе, по артиклу
    if (!in_array ($data[0], $artikulOld)) {
        fputs($fNew, $data[0]."\r\n");
    }
}
fclose($fIm);
fclose($fVtt);
fclose($fNew);



echo 'Программа обработки, анализа и подготовки прайс-листа ВТТ';
echo '<br>';
?>
