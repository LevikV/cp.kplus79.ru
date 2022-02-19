<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление аттрибутов</li>
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
                        <th>Имя этал. аттрибута</th>
                        <th>id этал. аттрибута</th>
                        <th>Имя аттрибута постав.</th>
                        <th>id аттрибута постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attribs_map_adds'])) {
                        $i = 1;
                        foreach ($data['attribs_map_adds'] as $attrib_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$attrib_map['id'] . '</td>';
                            echo '<td>' .$attrib_map['attrib_name'] . '</td>';
                            echo '<td>' .$attrib_map['attrib_id'] . '</td>';
                            echo '<td>' .$attrib_map['prov_attrib_name'] . '</td>';
                            echo '<td>' .$attrib_map['prov_attrib_id'] . '</td>';
                            echo '<td>' .$attrib_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список аттрибутов для добавления в эталонную базу</p>
                <table id="tableAttribs" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th>id аттрибута поставщика</th>
                        <th>Имя аттрибута поставщика</th>
                        <th>id группы аттриб поставщика</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['attribs_to_add'])) {
                        $i = 1;
                        foreach ($data['attribs_to_add'] as $attrib) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td class="prov-id">' .$attrib['provider_id'] . '</td>';
                            echo '<td>' .$attrib['provider_name'] . '</td>';
                            echo '<td class="attrib-id">' .$attrib['prov_attrib_id'] . '</td>';
                            echo '<td class="attrib-name">' .$attrib['prov_attrib_name'] . '</td>';
                            echo '<td class="prov-attrib-group-id">' .$attrib['prov_attrib_group_id'] . '</td>';
                            echo '<td><a class="link_add_attrib" href="#" data-prov-attrib-name="' . $attrib['prov_attrib_name'] .
                                '" data-prov-attrib-id="' . $attrib['prov_attrib_id'] . '" data-prov-name="' . $attrib['provider_name'] .
                                '" data-row-id="' . $i . '" data-prov-attrib-group-id="' . $attrib['prov_attrib_group_id'] .
                                '">Добавить</a></td>';
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
        </div>
    </div>
</div>