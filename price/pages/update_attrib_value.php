<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление знчений аттрибутов</li>
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
                        <th>Этал. знач. аттрибута</th>
                        <th>id этал. знач. аттрибута</th>
                        <th>Знач. аттрибута постав.</th>
                        <th>id знач. аттрибута постав.</th>
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

                        <th>id значения аттрибута поставщика</th>
                        <th>Значение аттрибута поставщика</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th>id аттриб поставщика</th>
                        <th>Имя аттриб поставщика</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attribs_to_add'])) {
                        $i = 1;
                        foreach ($data['attribs_to_add'] as $attrib_to_add) {
                            echo '<tr id="' . $i . '">';
                            echo '<td rowspan="2">' .$i . '</td>';
                            echo '<td class="attrib-id">' .$attrib_to_add['attrib_id'] . '</td>';
                            echo '<td class="attrib-name">' .$attrib['prov_attrib_name'] . '</td>';

                            echo '<td class="prov-id">' .$attrib['provider_id'] . '</td>';
                            echo '<td>' .$attrib['provider_name'] . '</td>';

                            echo '<td class="prov-attrib-group-id">' .$attrib['prov_attrib_group_id'] . '</td>';
                            echo '<td class="prov-attrib-name">' .$attrib_value['prov_attrib_name'] . '</td>';
                            echo '<td><a class="link_add_attrib" href="#" data-prov-attrib-name="' . $attrib['prov_attrib_name'] .
                                '" data-prov-attrib-id="' . $attrib['prov_attrib_id'] . '" data-prov-name="' . $attrib['provider_name'] .
                                '" data-row-id="' . $i . '" data-prov-attrib-group-id="' . $attrib['prov_attrib_group_id'] .
                                '">Добавить</a></td>';
                            echo '</tr>';
                            //
                            echo '<tr>';
                            echo '<td></td>';
                            echo '</tr>';

                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?
                if (!empty($data['attribs_to_add'])) {
                    echo '<p class="text-right m-0"><a class="link_add_attrib_all" href="#">Добавить все</a></p>';
                }
                ?>
            </div>
            <div class="col">
                <p class="mb-0">Список новых аттрибутов поставщика сопоставленных по имени, но разных по группам</p>
                <table id="tableCheckAttribs" class="table table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2">#</th>
                            <th>id аттриб. пост.</th>
                            <th>Имя аттриб. пост.</th>
                            <th>id группы аттриб. пост.</th>
                            <th>Имя группы аттриб. пост.</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>id похожего этал. аттриб.</th>
                            <th>Имя похожего этал. аттриб.</th>
                            <th>id группы похожего этал. аттриб.</th>
                            <th>Имя группы похожего этал. аттриб.</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attribs_check_to_add'])) {
                        $i = 1;
                        foreach ($data['attribs_check_to_add'] as $attrib_check_to_add) {
                            echo '<tr>';
                            echo '<td rowspan="2">' . $i . '</td>';
                            echo '<td>' . $attrib_check_to_add['prov_attrib_id'] . '</td>';
                            echo '<td>' . $attrib_check_to_add['prov_attrib_name'] . '</td>';
                            echo '<td>' . $attrib_check_to_add['prov_attrib_group_id'] . '</td>';
                            echo '<td rowspan="2">Сопоставить</td>';
                            echo '<td rowspan="2">Добавить</td>';
                            echo '</tr>';

                            echo '<tr>';
                            echo '<td>' . $attrib_check_to_add['similar_attrib_id'] . '</td>';
                            echo '<td>' . $attrib_check_to_add['similar_attrib_name'] . '</td>';
                            echo '<td>' . $attrib_check_to_add['similar_attrib_group_id'] . '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>