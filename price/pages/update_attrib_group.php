<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление групп аттрибутов</li>
            </ol>
            <div class="col">
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table id="tableMaps" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id</th>
                        <th>Имя этал. группы аттриб.</th>
                        <th>id этал. группы аттриб.</th>
                        <th>Имя группы аттриб. постав.</th>
                        <th>id группы аттриб. постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attrib_groups_map_adds'])) {
                        $i = 1;
                        foreach ($data['attrib_groups_map_adds'] as $attrib_group_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$attrib_group_map['id'] . '</td>';
                            echo '<td>' .$attrib_group_map['attrib_group_name'] . '</td>';
                            echo '<td>' .$attrib_group_map['attrib_group_id'] . '</td>';
                            echo '<td>' .$attrib_group_map['prov_attrib_group_name'] . '</td>';
                            echo '<td>' .$attrib_group_map['prov_attrib_group_id'] . '</td>';
                            echo '<td>' .$attrib_group_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список групп аттрибутов для добавления в эталонную базу</p>
                <table id="tableManufs" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th>id группы аттриб. поставщика</th>
                        <th>Имя группы аттриб. поставщика</th>
                        <th>id родит. группы аттриб. поставщика</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['manufs_to_add'])) {
                        $i = 1;
                        foreach ($data['manufs_to_add'] as $manuf) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$manuf['provider_id'] . '</td>';
                            echo '<td>' .$manuf['provider_name'] . '</td>';
                            echo '<td>' .$manuf['prov_manuf_id'] . '</td>';
                            echo '<td>' .$manuf['prov_manuf_name'] . '</td>';
                            echo '<td>' .$manuf['description'] . '</td>';
                            echo '<td>' .$manuf['image'] . '</td>';
                            echo '<td><a class="link_add_manuf" href="#" data-manuf-name="' . $manuf['prov_manuf_name'] .
                                '" data-prov-manuf-id="' . $manuf['prov_manuf_id'] . '" data-prov-name="' . $manuf['provider_name'] .
                                '" data-row-id="' . $i . '" data-manuf-descrip="' . $manuf['description'] .
                                '" data-manuf-image="' . $manuf['image'] . '">Добавить</a></td>';
                            echo '</tr>';
                            $i++;
                        }
                        echo '<tr>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td>Добавить все</td>';
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>