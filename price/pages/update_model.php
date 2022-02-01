<div class="container">
    <div class="row">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=price&code=main">Модуль управления Прайс листом</a></li>
                <li class="breadcrumb-item active">Обновление моделей</li>
            </ol>
            <div>
                <p class="mb-0">Список добавленных карт сопоставления</p>
                <table class="table">
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
                        echo '<tr>';
                        foreach ($data['models_map_adds'] as $model_map) {

                        }
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>