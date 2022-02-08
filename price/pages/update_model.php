<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление моделей</li>
            </ol>
            <div class="col">
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table id="tableMaps" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id</th>
                        <th>Имя этал. модели</th>
                        <th>id этал. модели</th>
                        <th>Имя модели постав.</th>
                        <th>id модели постав.</th>
                        <th>Поставщик</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['models_map_adds'])) {
                        $i = 1;
                        foreach ($data['models_map_adds'] as $model_map) {
                            echo '<tr>';
                            echo '<td>' .$i . '</td>';
                            echo '<td>' .$model_map['id'] . '</td>';
                            echo '<td>' .$model_map['model_name'] . '</td>';
                            echo '<td>' .$model_map['model_id'] . '</td>';
                            echo '<td>' .$model_map['prov_model_name'] . '</td>';
                            echo '<td>' .$model_map['prov_model_id'] . '</td>';
                            echo '<td>' .$model_map['provider_name'] . '</td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <p class="mb-0">Список моделей для добавления в эталонную базу</p>
                <table id="tableModels" class="table table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>id поставщика</th>
                        <th>Имя поставщика</th>
                        <th>id модели поставщика</th>
                        <th>Имя модели поставщика</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (!empty($data['models_to_add'])) {
                        $i = 1;
                        foreach ($data['models_to_add'] as $model) {
                            echo '<tr id="' . $i . '">';
                            echo '<td>' .$i . '</td>';
                            echo '<td class="prov-id">' .$model['provider_id'] . '</td>';
                            echo '<td>' .$model['provider_name'] . '</td>';
                            echo '<td class="model-id">' .$model['prov_model_id'] . '</td>';
                            echo '<td class="model-name">' .$model['prov_model_name'] . '</td>';
                            echo '<td><a class="link_add_model" href="#" data-model-name="' . $model['prov_model_name'] .
                                '" data-prov-model-id="' . $model['prov_model_id'] . '" data-prov-name="' . $model['provider_name'] .
                                '" data-row-id="' . $i . '">Добавить</a></td>';
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?
                if (!empty($data['models_to_add'])) {
                    echo '<p class="text-right m-0"><a class="link_add_model_all" href="#">Добавить все</a></p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>