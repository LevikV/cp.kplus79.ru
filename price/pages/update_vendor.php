<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление вендоров</li>
            </ol>
            <div class="col">
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table id="tableMaps" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id</th>
                        <th>Имя этал. вендора</th>
                        <th>id этал. вендора</th>
                        <th>Имя вендора постав.</th>
                        <th>id вендора постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['vendors_map_adds'])) {
                        $i = 1;
                        foreach ($data['vendors_map_adds'] as $vendor_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$vendor_map['id'] . '</td>';
                            echo '<td>' .$vendor_map['vendor_name'] . '</td>';
                            echo '<td>' .$vendor_map['vendor_id'] . '</td>';
                            echo '<td>' .$vendor_map['prov_vendor_name'] . '</td>';
                            echo '<td>' .$vendor_map['prov_vendor_id'] . '</td>';
                            echo '<td>' .$vendor_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список вендоров для добавления в эталонную базу</p>
                <table id="tableVendors" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th>id вендора поставщика</th>
                        <th>Имя вендора поставщика</th>
                        <th>Описание</th>
                        <th>Картинка</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['vendors_to_add'])) {
                        $i = 1;
                        foreach ($data['vendors_to_add'] as $vendor) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$vendor['provider_id'] . '</td>';
                            echo '<td>' .$vendor['provider_name'] . '</td>';
                            echo '<td>' .$vendor['prov_vendor_id'] . '</td>';
                            echo '<td>' .$vendor['prov_vendor_name'] . '</td>';
                            echo '<td>' .$vendor['description'] . '</td>';
                            echo '<td>' .$vendor['image'] . '</td>';
                            echo '<td><a class="link_add_vendor" href="#" data-vendor-name="' . $vendor['prov_vendor_name'] .
                                '" data-prov-vendor-id="' . $vendor['prov_vendor_id'] . '" data-vendor-name="' . $vendor['provider_name'] .
                                '" data-row-id="' . $i . '" data-vendor-descrip="' . $vendor['description'] .
                                '" data-vendor-image="' . $vendor['image'] . '">Добавить</a></td>';
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