<?php
class Price {
    public function updateProducts() {
        $db = new Db;

        // Устанавливаем счетчики
        $product_add_count = 0;
        $product_count_add_error = 0;
        $image_add_count = 0;
        $image_count_add_error = 0;
        $attrib_add_count = 0;
        $attrib_count_add_error = 0;

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

        // Проходимся по всем поставщикам, проверяем каждого и пытаемся обновиться по каждому поставщику
        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                // Перед загрузкой товаров необходимо проверить, сопоставлены ли основные данные
                // проверяем сопоставлены ли модели по текущему поставщику
                $provider_models = $db->getProviderModels($provider['id']);
                foreach ($provider_models as $provider_model) {
                    if (!in_array($provider_model['id'], $map_models_id, true)) {
                        // если сопоставления нет, то фиксируем в лог и прерываем обход
                        $db->addDetailLog('PRICE', 0, 'NEED_UPDATE_MODELS', 'provider_id', $provider['id']);
                        $flag_model = 0;
                        break;
                    }
                }
                // проверяем сопоставлены ли производители по текущему поставщику
                $provider_manufs = $db->getProviderManufs($provider['id']);
                foreach ($provider_manufs as $provider_manuf) {
                    if (!in_array($provider_manuf['id'], $map_manufs_id, true)) {
                        // если сопоставления нет, то фиксируем в лог и прерываем обход
                        $db->addDetailLog('PRICE', 0, 'NEED_UPDATE_MANUFS', 'provider_id', $provider['id']);
                        $flag_manuf = 0;
                        break;
                    }
                }
                // проверяем сопоставлены ли вендоры по текущему поставщику
                $provider_vendors = $db->getProviderVendors($provider['id']);
                foreach ($provider_vendors as $provider_vendor) {
                    if (!in_array($provider_vendor['id'], $map_vendors_id, true)) {
                        // если сопоставления нет, то фиксируем в лог и прерываем обход
                        $db->addDetailLog('PRICE', 0, 'NEED_UPDATE_VENDORS', 'provider_id', $provider['id']);
                        $flag_vendor = 0;
                        break;
                    }
                }

                // Смотрим результат проверки основных данных
                // если какие либо из основных данных не сопоставлены, то пропускаем текущего поставщика
                // и переходим к следующему
                if (($flag_model == 0) OR ($flag_manuf == 0) OR ($flag_vendor == 0)) {
                    // если сопоставления нет, то фиксируем в лог и прерываем обход
                    $db->addDetailLog('PRICE', 0, 'SKIP_UPDATE', 'provider_id', $provider['id']);
                    continue;
                }
                // Если на предидущем шаге выполнение не прервалось, то сначала
                // получаем все продукты из нашего прайса
                $products = array();
                $products = $db->getProducts();
                if ($products == false) {
                    // Ошибка получения списка продуктов из нашей базы, прерываем работу скрипта
                    continue;
                }

                // Получаем все товары текущего поставщика
                $provider_products = array();
                $provider_products = $db->getProviderProducts($provider['id']);

                // Для каждого товара поставщика проверяем, есть ли он в таблице сопоставлений
                foreach ($provider_products as $provider_product) {
                    if (!in_array($provider_product['id'], $map_products_id, true)) {
                        // если товар из таблицы поставщиков отсутствует в нашем прайсе, то его нужно сопоставить
                        // Проходим по каждому продукту из нашей базы и пытаемся найти соответствие
                        // но сначала смотрим, есть ли вообще продукты в эталонной базе, если их нет, то продукты
                        // от текущего поставщика будут назначены эталонными и все их данные (модели, вендоры,
                        // производители так же станут эталонными, т.е. первое заполнение базы)
                        if ($products != null) {
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
                                // 2) Пытаемся сопоставить по модели
                                if ($product['model_id'] == $db->getMapByProvItemId('model', $provider_product['model_id'])) {
                                    // Найдено соответствие по модели, ставим флаг
                                    $flag_model = 1;
                                }
                                // 3) Пытаемся сопоставить по производителю
                                if ($product['manufacturer_id'] == $db->getMapByProvItemId('manufacturer', $provider_product['manufacturer_id'])) {
                                    // Найдено соответствие по модели, ставим флаг
                                    $flag_manuf = 1;
                                }
                                // а здесь дальше надо написать код по сопоставлению или добавлению
                            }
                        } else {
                            // если база пустая, то подготавливаем данные и производим первоначальное заполнение
                            $data = array();
                            $data['name'] = $provider_product['name'];
                            $data['description'] = $provider_product['description'];
                            $data['category_id'] = $db->getMapByProvItemId('category', $provider_product['category_id']);
                            $data['model_id'] = $db->getMapByProvItemId('model', $provider_product['model_id']);
                            $data['vendor_id'] = $db->getMapByProvItemId('vendor', $provider_product['vendor_id']);
                            $data['manufacturer_id'] = $db->getMapByProvItemId('manufacturer', $provider_product['manufacturer_id']);
                            $data['width'] = $provider_product['width'];
                            $data['height'] = $provider_product['height'];
                            $data['length'] = $provider_product['length'];
                            $data['weight'] = $provider_product['weight'];
                            $data['version'] = $provider_product['version'];
                            $data['status'] = $provider_product['status'];
                            // Добавляем продукт в эталонную базу
                            $add_product_id = $db->addProduct($data);
                            if ($add_product_id) {
                                // Добавляем запись в карту сопоставлений
                                $map_id = $db->addMap('product', $add_product_id, $provider_product['id']);
                                // Увеличиваем счетчик добавленных товаров
                                $product_add_count++;
                                // Добавляем запись в детальный лог
                                $db->addDetailLog('PRICE', $add_product_id, 'ADD_PRODUCT', $provider['id'], $provider_product['name']);
                            } else {
                                // если произошла ошибка при добавлении товара пишем в лог и пропускаем товар
                                $db->addDetailLog('PRICE', 0, 'ERROR_ADD_PRODUCT', 'prov_prod_id', $provider_product['id']);
                                $product_count_add_error++;
                                continue;
                            }
                            // Добавляем изображение к новому продукту, если они имеются
                            $provider_product_images = $db->getProviderProductImages($provider['id'], $provider_product['id']);
                            if ($provider_product_images) {
                                foreach ($provider_product_images as $provider_product_image) {
                                    $data = array();
                                    $data['product_id'] = $add_product_id;
                                    $data['image'] = $provider_product_image['image'];
                                    $add_product_image_id = $db->addProductImage($data);
                                    if ($add_product_image_id) {
                                        // Увеличиваем счетчик добавленных изображений
                                        $image_add_count++;
                                        // Добавляем запись в детальный лог
                                        $db->addDetailLog('PRICE', $add_product_id, 'ADD_IMAGE', $provider['id'], $data['image']);
                                    } else {
                                        // если произошла ошибка при добавлении изображения пишем в лог и пропускаем текущее изобр
                                        $db->addDetailLog('PRICE', $add_product_id, 'ERROR_ADD_IMAGE', 'prov_prod_id', $provider_product['id']);
                                        $image_count_add_error++;
                                        continue;
                                    }
                                }
                            }
                            // Добавляем аттрибуты к новому продукту, если они имеются
                            $prov_prod_attribs = $db->getProviderProductAttributes($provider['id'], $provider_product['id']);
                            if ($prov_prod_attribs) {
                                foreach ($prov_prod_attribs as $prov_prod_attrib) {
                                    $data = array();
                                    $data['product_id'] = $add_product_id;
                                    $data['attribute_value_id'] = $db->getMapByProvItemId('attribute_value', $prov_prod_attrib['attribute_value_id']);
                                    $add_product_attribute_id = $db->addProductAttribute($data);
                                    if ($add_product_attribute_id) {
                                        // Увеличиваем счетчик добавленных аттрибутов
                                        $attrib_add_count++;
                                        // Добавляем запись в детальный лог
                                        $db->addDetailLog('PRICE', $add_product_id, 'ADD_ATTRIB_PRODUCT', '', $data['attribute_value_id']);
                                    } else {
                                        // если произошла ошибка при добавлении аттрибута пишем в лог и пропускаем текущий аттриб
                                        $db->addDetailLog('PRICE', $add_product_id, 'ERROR_ADD_ATTRIB_PROD', 'prov_prod_id', $provider_product['id']);
                                        $attrib_count_add_error++;
                                        continue;
                                    }
                                }
                            }


                        }
                    }
                }
            }
        }
    }

    public function updateModels() {
        $db = new Db;

        // Устанавливаем счетчики
        $product_add_count = 0;
        $product_count_add_error = 0;
        $image_add_count = 0;
        $image_count_add_error = 0;
        $attrib_add_count = 0;
        $attrib_count_add_error = 0;

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


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

        foreach ($providers as $provider) {
            $provider_models = $db->getProviderModels($provider['id']);
            $models = $db->getModels();
            foreach ($provider_models as $provider_model) {
                if (!in_array($provider_model['id'], $map_models_id)) {
                    //
                    if ($models != null) {
                        $flag_name = 0;
                        foreach ($models as $model) {
                            if (strcasecmp($model['name'], $provider_model['name']) == 0) {
                                // если имя модели из таблицы поставщиков равно имени эталонной модели, то
                                // необходимо ее сопоставить
                                $add_map_id = $db->addMap('model', $model['id'], $provider_model['id']);
                                $flag_name = 1;
                                break;
                            }
                        }
                        // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем новую модель в
                        // эталонную базу
                        if ($flag_name == 0) {

                        }
                    } else {

                    }

                }
            }
        }

    }

}
