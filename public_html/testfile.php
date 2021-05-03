<?php
$fNew = fopen('log/new_' . date("mdY") . '.log', 'w');
fputs($fNew, "В базу были добавлены новые товары: \r\n");
fclose($fNew);
?>
