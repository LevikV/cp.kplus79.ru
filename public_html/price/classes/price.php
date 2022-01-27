<?php
class Price {
    public function updateProducts() {
        $db = new Db;
        // Получаем все продукты из нашего прайса
        $products = array();
        $products = $db->getProducts();
        if ($products !== false) {
            if ($products == null) $products = array();
        } else {
            // Ошибка получения списка продуктов из нашей базы, прерываем работу скрипта
        }

        // Получаем список всех поставщиков
        $providers = $db->getProviders();

        // Получаем карту сопоставлений по товарам
        $maps = $db->getMaps('product');
        //Собираем id продуктов поставщиков сопоставленных с нашей эталонной базой
        $map_products_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_products_id[] = $map['provider_id'];
                }
            }
        }

        // Получаем карту сопоставлений по моделям
        $maps = $db->getMaps('model');
        //Собираем id моделей поставщиков сопоставленных с нашей эталонной базой
        $map_models_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_models_id[] = $map['provider_id'];
                }
            }
        }

        // Получаем карту сопоставлений по производителям
        $maps = $db->getMaps('manufacturer');
        //Собираем id производителей поставщиков сопоставленных с нашей эталонной базой
        $map_manufs_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_manufs_id[] = $map['provider_id'];
                }
            }
        }

        // Получаем карту сопоставлений по вендорам
        $maps = $db->getMaps('vendor');
        //Собираем id вендоров поставщиков сопоставленных с нашей эталонной базой
        $map_vendors_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_vendors_id[] = $map['provider_id'];
                }
            }
        }

        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                // Перед загрузкой товаров необходимо проверить, сопоставлены ли основные данные
                // проверяем сопоставлены ли модели по текущему поставщику
                $provider_models = $db->getProviderModels($provider['id']);
                foreach ($provider_models as $provider_model) {
                    if (!in_array($provider_model['id'], $map_models_id, true)) {
                        // Если модель поставщика не сопоставлена с моделью из нашей базы
                        // то пропускаем текущего поставщика и записываем сообщение о необходимости
                        // обновить основные данные по текущему поставщику


                    }
                }


                // Получаем все товары текущего поставщика
                $provider_products = array();
                $provider_products = $db->getProviderProducts($provider['id']);
                // Для каждого товара поставщика проверяем, есть ли он в таблице сопоставлений
                foreach ($provider_products as $provider_product) {
                    if (!in_array($provider_product['id'], $map_products_id, true)) {
                        // если товар из таблицы поставщиков отсутствует в нашем прайсе, то его нужно сопоставить
                        // Проходим по каждому продукту из нашей базы и пытаемся найти соответствие
                        foreach ($products as $product) {
                            // обнуляем флаги соответствия
                            $flag_name = 0;
                            $flag_model = 0;
                            $flag_manuf = 0;
                            // 1) Пытаемся сопоставить по имени
                            if ($product['name'] == $provider_product['name']) {
                                // Найдено соответствие по имени, ставим флаг
                                $flag_name = 1;
                            }

                        }
                    }
                }
            }
        }
    }
}
