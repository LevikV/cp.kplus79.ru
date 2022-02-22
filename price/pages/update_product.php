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
                    if (!empty($data['attrib_values_map_adds'])) {
                        $i = 1;
                        foreach ($data['attrib_values_map_adds'] as $attrib_value_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$attrib_value_map['id'] . '</td>';
                            echo '<td>' .$attrib_value_map['attrib_value'] . '</td>';
                            echo '<td>' .$attrib_value_map['attrib_value_id'] . '</td>';
                            echo '<td>' .$attrib_value_map['prov_attrib_value'] . '</td>';
                            echo '<td>' .$attrib_value_map['prov_attrib_value_id'] . '</td>';
                            echo '<td>' .$attrib_value_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список значений аттрибутов для добавления в эталонную базу</p>
                <table id="tableAttribValues" class="table table-sm">
                    <thead>
                    <tr>
                        <th rowspan="2">#</th>
                        <th>id эталонного аттрибута</th>
                        <th>Имя эталонного аттрибута</th>
                        <th rowspan="2">id значения аттрибута поставщика</th>
                        <th rowspan="2">Значение аттрибута поставщика</th>
                        <th rowspan="2">id поставщика</th>
                        <th rowspan="2">Имя поставщика</th>
                        <th rowspan="2"></th>
                        <th rowspan="2"></th>
                    </tr>
                    <tr>
                        <th>id аттриб поставщика</th>
                        <th>Имя аттриб поставщика</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attrib_values_to_add'])) {
                        $i = 1;
                        foreach ($data['attrib_values_to_add'] as $attrib_value_to_add) {
                            echo '<tr id="' . $i . '">';
                            echo '<td rowspan="2">' .$i . '</td>';
                            echo '<td class="attrib-id">' .$attrib_value_to_add['attrib_id'] . '</td>';
                            echo '<td class="attrib-name">' .$attrib_value_to_add['attrib_name'] . '</td>';
                            echo '<td class="prov-attrib-value-id" rowspan="2">' .$attrib_value_to_add['prov_attrib_value_id'] . '</td>';
                            echo '<td class="prov-attrib-value" rowspan="2">' .$attrib_value_to_add['prov_attrib_value'] . '</td>';
                            echo '<td class="prov-id" rowspan="2">' .$attrib_value_to_add['provider_id'] . '</td>';
                            echo '<td class="prov-name" rowspan="2">' .$attrib_value_to_add['provider_name'] . '</td>';
                            echo '<td rowspan="2">Сопоставить</td>';
                            echo '<td rowspan="2"><a class="link_add_attrib_value" href="#" ' .
                                'data-row-id="' . $i .
                                '" data-attrib-id="' . $attrib_value_to_add['attrib_id'] .
                                '" data-attrib-name="' . $attrib_value_to_add['attrib_name'] .
                                '" data-prov-attrib-id="' . $attrib_value_to_add['prov_attrib_id'] .
                                '" data-prov-attrib-name="' . $attrib_value_to_add['prov_attrib_name'] .
                                '" data-prov-attrib-value-id="' . $attrib_value_to_add['prov_attrib_value_id'] .
                                '" data-prov-attrib-value="' . $attrib_value_to_add['prov_attrib_value'] .
                                '" data-prov-name="' . $attrib_value_to_add['provider_name'] .
                                '" data-prov-id="' . $attrib_value_to_add['provider_id'] .
                                '">Добавить</a></td>';
                            echo '</tr>';
                            //
                            echo '<tr id="' . $i . '-child">';
                            echo '<td class="prov-attrib-id">' .$attrib_value_to_add['prov_attrib_id'] . '</td>';
                            echo '<td class="prov-attrib-name">' .$attrib_value_to_add['prov_attrib_name'] . '</td>';
                            echo '</tr>';

                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?
                if (!empty($data['attrib_values_to_add'])) {
                    echo '<p class="text-right m-0"><a class="link_add_attrib_values_all" href="#">Добавить все</a></p>';
                }
                ?>
            </div>

        </div>
    </div>
</div>