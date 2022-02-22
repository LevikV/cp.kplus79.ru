<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление категорий</li>
            </ol>
            <div class="col">
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table id="tableMaps" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id</th>
                        <th>Имя этал. категории</th>
                        <th>id этал. категории</th>
                        <th>Имя категории постав.</th>
                        <th>id категории постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['categories_map_adds'])) {
                        $i = 1;
                        foreach ($data['categories_map_adds'] as $category_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$category_map['id'] . '</td>';
                            echo '<td>' .$category_map['category_name'] . '</td>';
                            echo '<td>' .$category_map['category_id'] . '</td>';
                            echo '<td>' .$category_map['prov_category_name'] . '</td>';
                            echo '<td>' .$category_map['prov_category_id'] . '</td>';
                            echo '<td>' .$category_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список категорий для добавления в эталонную базу</p>
                <table id="tableCategories" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th>id категории поставщика</th>
                        <th>Имя категории поставщика</th>
                        <th>id родит. категории поставщика</th>
                        <th>Имя родит. категории поставщика</th>
                        <th>Описание категории поставщика</th>
                        <th>Изображение категории поставщика</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['categories_to_add'])) {
                        $i = 1;
                        foreach ($data['categories_to_add'] as $category_to_add) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td class="prov-id">' .$category_to_add['provider_id'] . '</td>';
                            echo '<td>' .$category_to_add['provider_name'] . '</td>';
                            echo '<td class="category-id">' .$category_to_add['prov_category_id'] . '</td>';
                            echo '<td class="category-name">' .$category_to_add['prov_category_name'] . '</td>';
                            echo '<td class="prov-category-parent-id">' .$category_to_add['prov_category_parent_id'] . '</td>';
                            echo '<td class="prov-category-parent-name">' .$category_to_add['prov_category_parent_name'] . '</td>';
                            echo '<td class="prov-category-description">' .$category_to_add['prov_category_description'] . '</td>';
                            echo '<td class="prov-category-image">' .$category_to_add['prov_category_image'] . '</td>';
                            echo '<td rowspan="2">Сопоставить</td>';
                            echo '<td rowspan="2"><a class="link_add_category" href="#" ' .
                                'data-row-id="' . $i .
                                '" data-prov-category-id="' . $category_to_add['prov_category_id'] .
                                '" data-prov-category-name="' . $category_to_add['prov_category_name'] .
                                '" data-prov-category-parent-id="' . $category_to_add['prov_category_parent_id'] .
                                '" data-prov-category-description="' . $category_to_add['prov_category_description'] .
                                '" data-prov-category-image="' . $category_to_add['prov_category_image'] .
                                '" data-prov-name="' . $category_to_add['provider_name'] .
                                '" data-prov-id="' . $category_to_add['provider_id'] .
                                '">Добавить</a></td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?
                if (!empty($data['categories_to_add'])) {
                    echo '<p class="text-right m-0"><a class="link_add_category_all" href="#">Добавить все</a></p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>