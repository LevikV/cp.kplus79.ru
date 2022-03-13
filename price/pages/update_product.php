<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление товаров</li>
            </ol>
            <?
            if (isset($data['warning'])) {
                echo '<div class="col">';
                foreach ($data['warning'] as $warning) {
                    echo '<p class="text-danger">'. $warning . '</p>';
                }
                echo '</div>';

            }
            ?>
            <div class="col">
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table id="tableMaps" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id</th>
                        <th>Этал. имя товара</th>
                        <th>id этал. товара</th>
                        <th>Имя товара постав.</th>
                        <th>id товара постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['products_map_adds'])) {
                        $i = 1;
                        foreach ($data['products_map_adds'] as $product_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$product_map['id'] . '</td>';
                            echo '<td>' .$product_map['product_name'] . '</td>';
                            echo '<td>' .$product_map['product_id'] . '</td>';
                            echo '<td>' .$product_map['prov_product_name'] . '</td>';
                            echo '<td>' .$product_map['prov_product_id'] . '</td>';
                            echo '<td>' .$product_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список товаров для добавления в эталонную базу</p>
                <table id="tableProducts" class="table table-sm">
                    <thead>
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2">id товара</th>
                        <th rowspan="2">Наименование</th>
                        <th rowspan="2">Описание</th>
                        <th>id категории поставщика</th>
                        <th>id модели поставщика</th>
                        <th>id вендора поставщика</th>
                        <th>id производит. поставщика</th>
                        <th rowspan="2">Аттрибуты</th>
                        <th rowspan="2">Ширина</th>
                        <th rowspan="2">Высота</th>
                        <th rowspan="2">Длина</th>
                        <th rowspan="2">Вес</th>
                        <th rowspan="2">Версия</th>
                        <th rowspan="2">Изображения</th>
                        <th rowspan="2">Статус</th>
                        <th rowspan="2">Дата добавления</th>
                        <th rowspan="2">Дата редактирования</th>
                        <th rowspan="2">Дата обновления</th>

                        <th rowspan="2">id поставщика</th>
                        <th rowspan="2">Имя поставщика</th>
                    </tr>
                    <tr>
                        <th>Имя категории</th>
                        <th>Имя модели</th>
                        <th>Имя вендора</th>
                        <th>Имя производит.</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['products_to_add'])) {
                        $i = 1;
                        foreach ($data['products_to_add'] as $product_to_add) {
                            echo '<tr id="' . $i . '">';
                            echo '<td rowspan="2">' .$i . '</td>';
                            echo '<td rowspan="2" class="prov-product-id">' .$product_to_add['prov_product_id'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-name">' .$product_to_add['prov_product_name'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-description">' .$product_to_add['prov_product_description'] . '</td>';
                            echo '<td class="prov-product-category-id">' .$product_to_add['prov_product_category_id'] . '</td>';
                            echo '<td class="prov-product-model-id">' .$product_to_add['prov_product_model_id'] . '</td>';
                            echo '<td class="prov-product-vendor-id">' .$product_to_add['prov_product_vendor_id'] . '</td>';
                            echo '<td class="prov-product-manuf-id">' .$product_to_add['prov_product_manuf_id'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-attribs">';
                            if ($product_to_add['prov_product_attribs']) {
                                foreach ($product_to_add['prov_product_attribs'] as $attrib) {
                                    echo $attrib['attribute_name'] . ': ' . $attrib['attribute_value'] . '<br>';
                                }
                            }
                            echo '</td>';
                            echo '<td rowspan="2" class="prov-product-width">' .$product_to_add['prov_product_width'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-height">' .$product_to_add['prov_product_height'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-length">' .$product_to_add['prov_product_length'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-weight">' .$product_to_add['prov_product_weight'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-version">' .$product_to_add['prov_product_version'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-images">';
                            if ($product_to_add['prov_product_images']) {
                                foreach ($product_to_add['prov_product_images'] as $image) {
                                    echo $image['image'] . '<br>';
                                }
                            }
                            echo '</td>';
                            echo '<td rowspan="2" class="prov-product-status">' .$product_to_add['prov_product_status'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-date-add">' .$product_to_add['prov_product_date_add'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-date-edit">' .$product_to_add['prov_product_date_edit'] . '</td>';
                            echo '<td rowspan="2" class="prov-product-date-update">' .$product_to_add['prov_product_date_update'] . '</td>';
                            echo '<td rowspan="2" class="prov-id">' .$product_to_add['provider_id'] . '</td>';
                            echo '<td rowspan="2" class="prov-name">' .$product_to_add['provider_name'] . '</td>';
                            echo '<td rowspan="2">Сопоставить</td>';
                            echo '<td rowspan="2"><a class="link_add_product" href="#" ' .
                                'data-row-id="' . $i .
                                '" data-prov-product-id="' . $product_to_add['prov_product_id'] .
                                '">Добавить</a></td>';
                            echo '</tr>';
                            //
                            echo '<tr id="' . $i . '-child">';
                            echo '<td class="prov-product-category-name">' .$product_to_add['prov_product_category_name'] . '</td>';
                            echo '<td class="prov-product-model-name">' .$product_to_add['prov_product_model_name'] . '</td>';
                            echo '<td class="prov-product-vendor-name">' .$product_to_add['prov_product_vendor_name'] . '</td>';
                            echo '<td class="prov-product-manuf-name">' .$product_to_add['prov_product_manuf_name'] . '</td>';
                            echo '</tr>';

                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?
                if (!empty($data['products_to_add'])) {
                    echo '<p class="text-right m-0"><a class="link_add_products_all" href="#">Добавить все</a></p>';
                }
                ?>
            </div>

        </div>
    </div>
</div>