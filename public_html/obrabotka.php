<?php
$kurs = 77;
//
$artikulOld = array();
$fOld = fopen('vtt_price_im.csv', 'r');
$i = 0;
while (($data = fgetcsv($fOld, 0, ';', '"')) !== FALSE) {
    if ($i == 1) $artikulOld[] = $data[0];
    $i = 1;
}
fclose($fOld);
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
//
$fVtt = fopen('vtt_price_all_new.csv','r');
$fIm = fopen('vtt_price_im.csv', 'w');
$fNew = fopen('new_goods.txt', 'w');
//Записываем строку заголовков
$caption = array("Artikul", "Name", "Manafacture", "Description", "Group0", "Group1", "Group2", "Quantity", "Price", "Width",
    "Height", "Depth", "Weight", "PhotoUrl", "PartNumber", "Vendor", "Compatibility", "ColorName");
fputcsv($fIm, $caption, ';', '"');
$i = 0;
while (($data = fgetcsv($fVtt, 0, ';', '"')) !== FALSE) {
    if ($i>0) {
        //Накрутка цены, в зависимости оригинал или нет
        if ((stripos($data[1], '(o)')===false) AND (stripos($data[1], '( o )')===false) AND (stripos($data[1], '(О)')===false) AND (stripos($data[1], '( о )')===false)) {
            $price = $data[8];
            $price = $price*$kurs+$price*$kurs*0.4;
            //Если цена равна нулю, то проверяем, не заполнена ли уже цена в ИМ
            if ($price == 0) {
                for ($k = 0; $k < count($imVttPriceNotNull); $k++) {
                    if ($data[0] == $imVttPriceNotNull[$k][0]) {
                        $price = $imVttPriceNotNull[$k][1];
                        echo '$data[1]';
                        echo '<br>';
                        break;
                    }
                }
            }
            if ($price === 0) $price = '';
            $data[8] = $price;
        } else {
            $price = $data[8];
            $price = $price*$kurs+$price*$kurs*0.1;
            //Если цена равна нулю, то проверяем, не заполнена ли уже цена в ИМ
            if ($price == 0) {
                for ($k = 0; $k < count($imVttPriceNotNull); $k++) {
                    if ($data[0] == $imVttPriceNotNull[$k][0]) {
                        $price = $imVttPriceNotNull[$k][1];
                        echo '$data[1]';
                        echo '<br>';
                        break;
                    }
                }
            }
            if ($price === 0) $price = '';
            $data[8] = $price;
        }
        //
        //$data[16] = str_replace(array("\r\n", "\r", "\n"), '', $data[16]);
        //Записываем прайс лист для ИМ
        fputcsv($fIm, $data, ';', '"');
        //Проверяем новый это товар или уже есть в базе, по артиклу
        if (!in_array ($data[0], $artikulOld)) {
            echo "Строка в прайс листе: " . $i . " Артикул: " . $data[0] . " Наименование: " . $data[1] . "<br>";
            fputs($fNew, $data[0]."\r\n");
        }
    }
    $i++;
}
fclose($fIm);
fclose($fVtt);
fclose($fNew);



echo 'Программа обработки, анализа и подготовки прайс-листа ВТТ';
echo '<br>';
?>
