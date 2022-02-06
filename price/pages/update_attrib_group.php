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
                <table id="tableAttribGroups" class="table table-sm">
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
                    if (!empty($data['attrib_groups_to_add'])) {
                        $i = 1;
                        foreach ($data['attrib_groups_to_add'] as $attrib_group) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$attrib_group['provider_id'] . '</td>';
                            echo '<td>' .$attrib_group['provider_name'] . '</td>';
                            echo '<td>' .$attrib_group['prov_attrib_group_id'] . '</td>';
                            echo '<td>' .$attrib_group['prov_attrib_group_name'] . '</td>';
                            echo '<td>' .$attrib_group['prov_attrib_group_parent_id'] . '</td>';
                            echo '<td><a class="link_add_attrib_group" href="#" data-attrib-group-name="' . $attrib_group['prov_attrib_group_name'] .
                                '" data-prov-attrib-group-id="' . $attrib_group['prov_attrib_group_id'] . '" data-prov-name="' . $attrib_group['provider_name'] .
                                '" data-row-id="' . $i . '" data-attrib-group-parent-id="' . $attrib_group['prov_attrib_group_parent_id'] .
                                '">Добавить</a></td>';
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