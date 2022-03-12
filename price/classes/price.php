<?php
class Price {
    public function addModelFromProv($data) {
        // Метод добавления модели в эатлонную базу из модели поставщика
        // поэтому происходит два действия
        // 1) Добавление модели в эталонную базу
        // 2) Добавление карты сопоставления
        $db = new Db;
        $temp = array();
        $temp['name'] = $data['name'];
        $model_id = $db->addModel($temp);
        if ($model_id) {
            $map_id = $db->addMap('model', $model_id, $data['prov_model_id']);
        }
    }

    public function updateProducts() {
        $db = new Db;
        $products_map_adds = array();
        $products_to_add = array();
        $warning = array();

        // Устанавливаем счетчики
        $product_add_count = 0;
        $product_count_add_error = 0;
        $image_add_count = 0;
        $image_count_add_error = 0;
        $attrib_add_count = 0;
        $attrib_count_add_error = 0;

        // Получаем список всех поставщиков
        $providers = $db->getProviders();

        // Проходимся по всем поставщикам, проверяем каждого и пытаемся обновиться по каждому поставщику
        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                // Перед загрузкой товаров необходимо проверить, сопоставлены ли основные данные
                $flag_maps = array();
                $data = array();
                $flag_maps['model'] = $db->checkMapProviderModels($provider['id']);
                $flag_maps['vendor'] = $db->checkMapProviderVendors($provider['id']);
                $flag_maps['manufacturer'] = $db->checkMapProviderManufs($provider['id']);
                $flag_maps['attribute_group'] = $db->checkMapProviderAttributeGroups($provider['id']);
                $flag_maps['attribute'] = $db->checkMapProviderAttributes($provider['id']);
                $flag_maps['attribute_value'] = $db->checkMapProviderAttributeValues($provider['id']);

                foreach ($flag_maps as $key => $flag) {
                    if ($flag) {
                        continue;
                    } else {
                        $data['warning'][] = 'Необходимо обновить ' . $key . ' у поставщика ' . $provider['id'];
                    }
                }

                // Смотрим результат проверки основных данных
                // если какие либо из основных данных не сопоставлены, то пропускаем текущего поставщика
                // и переходим к следующему
                if (isset($data['warning'])) {
                    // если сопоставления нет, то фиксируем в лог и прерываем обход
                    //$db->addDetailLog('PRICE', 0, 'SKIP_UPDATE', 'provider_id', $provider['id']);
                    $warning[] = $data['warning'];
                    $warning[] = 'Обновление по поставщику id = ' . $provider['id'] . ' пропущено.';
                    continue;
                }
                // Если на предидущем шаге выполнение не прервалось, то сначала
                // получаем все продукты из нашего прайса
                $products = $db->getProducts();
                if (($products == null) OR ($products == false)) {
                    $products = array();
                }

                // Получаем все товары текущего поставщика
                $provider_products = $db->getProviderProducts($provider['id']);
                if (($provider_products == null) OR ($provider_products == false)) {
                    $provider_products = array();
                }

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

                // Для каждого товара поставщика проверяем, есть ли он в таблице сопоставлений
                foreach ($provider_products as $provider_product) {
                    if (!in_array($provider_product['id'], $map_products_id, true)) {
                        // если товар из таблицы поставщиков отсутствует в нашем прайсе, то его нужно сопоставить
                        // Проходим по каждому продукту из нашей базы и пытаемся найти соответствие
                        // но сначала смотрим, есть ли вообще продукты в эталонной базе, если их нет, то продукты
                        // от текущего поставщика будут назначены эталонными и все их данные (модели, вендоры,
                        // производители так же станут эталонными, т.е. первое заполнение базы)

                        // Пропускаем отключенные товары и товары на проверке
                        if ($provider_product['status'] != 1) continue;

                        if (!empty($products)) {
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
                                if ((int)$product['model_id'] == (int)$db->getMapByProvItemId('model', $provider_product['model_id'])) {
                                    // Найдено соответствие по модели, ставим флаг
                                    $flag_model = 1;
                                }
                                // 3) Пытаемся сопоставить по производителю
                                if ((int)$product['manufacturer_id'] == (int)$db->getMapByProvItemId('manufacturer', $provider_product['manufacturer_id'])) {
                                    // Найдено соответствие по модели, ставим флаг
                                    $flag_manuf = 1;
                                }

                                //
                                if (($flag_name == 0) OR ($flag_model == 0) OR ($flag_manuf == 0)) {
                                    continue;
                                } else {
                                    // Если товар удалось сопоставить по имени, модели и производителю, то добавляем
                                    // карту сопоставления по данному товару
                                    $add_map_id = $db->addMap('product', $product['id'], $provider_product['id']);
                                    // формируем массив для передачи в отображение
                                    $products_map_adds[] = array(
                                        'id' => $add_map_id,
                                        'product_id' => $product['id'],
                                        'product_name' => $product['name'],
                                        'prov_product_id' => $provider_product['id'],
                                        'prov_product_name' => $provider_product['name'],
                                        'provider_name' => $provider['name']
                                    );

                                    break;
                                }
                            }
                            //
                            if (($flag_name == 0) OR ($flag_model == 0) OR ($flag_manuf == 0)) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $category = $db->getProviderModel($provider_product['category_id']);
                                $model = $db->getProviderModel($provider_product['model_id']);
                                $vendor = $db->getProviderVendor($provider_product['vendor_id']);
                                $manufacturer = $db->getProviderManufacturer($provider_product['manufacturer_id']);
                                $products_to_add[] = array(
                                    'prov_product_id' => $provider_product['id'],
                                    'prov_product_name' => $provider_product['name'],
                                    'prov_product_description' => $provider_product['description'],
                                    'prov_product_category_id' => $provider_product['category_id'],
                                    'prov_product_model_id' => $provider_product['model_id'],
                                    'prov_product_vendor_id' => $provider_product['vendor_id'],
                                    'prov_product_manuf_id' => $provider_product['manufacturer_id'],
                                    'prov_product_attribs' => $provider_product['attributes'],
                                    'prov_product_width' => $provider_product['width'],
                                    'prov_product_height' => $provider_product['height'],
                                    'prov_product_length' => $provider_product['length'],
                                    'prov_product_weight' => $provider_product['weight'],
                                    'prov_product_version' => $provider_product['version'],
                                    'prov_product_images' => $provider_product['images'],
                                    'prov_product_status' => $provider_product['status'],
                                    'prov_product_date_add' => $provider_product['date_add'],
                                    'prov_product_date_edit' => $provider_product['date_edit'],
                                    'prov_product_date_update' => $provider_product['date_update'],
                                    'prov_product_category_name' => $category['name'],
                                    'prov_product_model_name' => $model['name'],
                                    'prov_product_vendor_name' => $vendor['name'],
                                    'prov_product_manuf_name' => $manufacturer['name'],
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name']
                                );
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
                                //
                                $products_map_adds[] = array(
                                    'id' => $map_id,
                                    'product_id' => $add_product_id,
                                    'product_name' => $provider_product['name'],
                                    'prov_product_id' => $provider_product['id'],
                                    'prov_product_name' => $provider_product['name'],
                                    'provider_name' => $provider['name']
                                );
                                // Добавляем запись в детальный лог
                                //$db->addDetailLog('PRICE', $add_product_id, 'ADD_PRODUCT', $provider['id'], $provider_product['name']);
                            } else {
                                // если произошла ошибка при добавлении товара пишем в лог и пропускаем товар
                                //$db->addDetailLog('PRICE', 0, 'ERROR_ADD_PRODUCT', 'prov_prod_id', $provider_product['id']);
                                $warning[] = 'Ошибка при добавлении товара в эталонную базу. Поставщик id: ' . $provider['id'] . ' Товар поставщика id: ' . $provider_product['id'];
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
                                        //$db->addDetailLog('PRICE', $add_product_id, 'ADD_IMAGE', $provider['id'], $data['image']);
                                    } else {
                                        // если произошла ошибка при добавлении изображения пишем в лог и пропускаем текущее изобр
                                        // $db->addDetailLog('PRICE', $add_product_id, 'ERROR_ADD_IMAGE', 'prov_prod_id', $provider_product['id']);
                                        $warning[] = 'Ошибка при добавлении изображения товара в эталонную базу. Поставщик id: ' . $provider['id'] .
                                            ' Товар поставщика id: ' . $provider_product['id'] . ' Эталонный товар id: ' . $add_product_id;
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
                                        //$db->addDetailLog('PRICE', $add_product_id, 'ADD_ATTRIB_PRODUCT', '', $data['attribute_value_id']);
                                    } else {
                                        // если произошла ошибка при добавлении аттрибута пишем в лог и пропускаем текущий аттриб
                                        //$db->addDetailLog('PRICE', $add_product_id, 'ERROR_ADD_ATTRIB_PROD', 'prov_prod_id', $provider_product['id']);
                                        $warning[] = 'Ошибка при добавлении аттрибута товара в эталонную базу. Поставщик id: ' . $provider['id'] .
                                            ' Товар поставщика id: ' . $provider_product['id'] . ' Эталонный товар id: ' . $add_product_id;
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
        // Возвращаем полученные данные
        $data = array();
        if (!empty($warning)) {
            $data['warning'] = $warning;
        }
        //
        if ($product_add_count != 0) {
            $data['warning'][] = 'Произведено первоначальное заполнение эталонной базы.';
            $data['warning'][] = 'Добавлено новых товаров: ' . $product_add_count . '. К ним добавлено изображений: ' . $image_add_count . ' и аттрибутов: ' . $attrib_add_count;
        }
        $data['products_map_adds'] = $products_map_adds;
        $data['products_to_add'] = $products_to_add;
        return $data;
    }

    public function updateModels() {
        // Метод обновления моделей
        // Возвращает массив данных $data с ключами:
        // - models_map_adds - массив добавленных автоматически карт сопоставления по моделям
        // - models_to_add - массив моделей для добавления в эталонную базу

        $db = new Db;
        $models_to_add = array();
        $models_map_adds = array();

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
            if ($provider['parent_id'] == null) {
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
                                    // Добавляем запись в детальный лог
                                    $db->addDetailLog('PRICE', '0', 'ADD_MAP_MODEL', $model['name'], $provider_model['name']);
                                    // формируем массив для передачи в отображение
                                    $models_map_adds[] = array(
                                        'id' => $add_map_id,
                                        'model_id' => $model['id'],
                                        'model_name' => $model['name'],
                                        'prov_model_id' => $provider_model['id'],
                                        'prov_model_name' => $provider_model['name'],
                                        'provider_name' => $provider['name']
                                    );
                                    break;
                                }
                            }
                            // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем новую модель в
                            // эталонную базу
                            if ($flag_name == 0) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $models_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_model_id' => $provider_model['id'],
                                    'prov_model_name' => $provider_model['name']
                                );
                            }
                        } else {
                            // формируем массив для передачи в отображение т.к. добавляться новые значения
                            // пока будут только вручную
                            $models_to_add[] = array(
                                'provider_id' => $provider['id'],
                                'provider_name' => $provider['name'],
                                'prov_model_id' => $provider_model['id'],
                                'prov_model_name' => $provider_model['name']
                            );
                        }

                    }
                }
            }

        }

        // Возвращаем полученные данные
        $data = array();
        $data['models_map_adds'] = $models_map_adds;
        $data['models_to_add'] = $models_to_add;
        return $data;
    }

    public function updateVendors() {
        // Метод обновления вендоров
        // Возвращает массив данных $data с ключами:
        // - vendors_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - vendors_to_add - массив вендоров для добавления в эталонную базу

        $db = new Db;
        $vendors_to_add = array();
        $vendors_map_adds = array();

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


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
                $provider_vendors = $db->getProviderVendors($provider['id']);
                $vendors = $db->getVendors();
                foreach ($provider_vendors as $provider_vendor) {
                    if (!in_array($provider_vendor['id'], $map_vendors_id)) {
                        //
                        if ($vendors != null) {
                            $flag_name = 0;
                            foreach ($vendors as $vendor) {
                                if (strcasecmp($vendor['name'], $provider_vendor['name']) == 0) {
                                    // если имя вендора из таблицы поставщиков равно имени эталонного вендора, то
                                    // необходимо его сопоставить
                                    $add_map_id = $db->addMap('vendor', $vendor['id'], $provider_vendor['id']);
                                    $flag_name = 1;
                                    // Добавляем запись в детальный лог
                                    $db->addDetailLog('PRICE', '0', 'ADD_MAP_VENDOR', $vendor['name'], $provider_vendor['name']);
                                    // формируем массив для передачи в отображение
                                    $vendors_map_adds[] = array(
                                        'id' => $add_map_id,
                                        'vendor_id' => $vendor['id'],
                                        'vendor_name' => $vendor['name'],
                                        'prov_vendor_id' => $provider_vendor['id'],
                                        'prov_vendor_name' => $provider_vendor['name'],
                                        'provider_name' => $provider['name']
                                    );
                                    break;
                                }
                            }
                            // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем нового вендора в
                            // эталонную базу
                            if ($flag_name == 0) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $vendors_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_vendor_id' => $provider_vendor['id'],
                                    'prov_vendor_name' => $provider_vendor['name'],
                                    'prov_vendor_descrip' => $provider_vendor['description'],
                                    'prov_vendor_image' => $provider_vendor['image']
                                );
                            }
                        } else {
                            // формируем массив для передачи в отображение т.к. добавляться новые значения
                            // пока будут только вручную
                            $vendors_to_add[] = array(
                                'provider_id' => $provider['id'],
                                'provider_name' => $provider['name'],
                                'prov_vendor_id' => $provider_vendor['id'],
                                'prov_vendor_name' => $provider_vendor['name'],
                                'prov_vendor_descrip' => $provider_vendor['description'],
                                'prov_vendor_image' => $provider_vendor['image']
                            );
                        }

                    }
                }
            }

        }

        // Возвращаем полученные данные
        $data = array();
        $data['vendors_map_adds'] = $vendors_map_adds;
        $data['vendors_to_add'] = $vendors_to_add;
        return $data;
    }

    public function updateManufs() {
        // Метод обновления производителей
        // Возвращает массив данных $data с ключами:
        // - manufs_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - manufs_to_add - массив вендоров для добавления в эталонную базу

        $db = new Db;
        $manufs_to_add = array();
        $manufs_map_adds = array();

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


        // Получаем карту сопоставлений по вендорам
        $maps = $db->getMaps('manufacturer');
        //Собираем id вендоров поставщиков сопоставленных с нашей эталонной базой
        $map_manufs_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_manufs_id[] = $map['provider_id'];
                }
            }
        }

        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $provider_manufs = $db->getProviderManufs($provider['id']);
                $manufs = $db->getManufs();
                foreach ($provider_manufs as $provider_manuf) {
                    if (!in_array($provider_manuf['id'], $map_manufs_id)) {
                        //
                        if ($manufs != null) {
                            $flag_name = 0;
                            foreach ($manufs as $manuf) {
                                if (strcasecmp($manuf['name'], $provider_manuf['name']) == 0) {
                                    // если имя вендора из таблицы поставщиков равно имени эталонного вендора, то
                                    // необходимо его сопоставить
                                    $add_map_id = $db->addMap('manufacturer', $manuf['id'], $provider_manuf['id']);
                                    $flag_name = 1;
                                    // Добавляем запись в детальный лог
                                    $db->addDetailLog('PRICE', '0', 'ADD_MAP_MANUF', $manuf['name'], $provider_manuf['name']);
                                    // формируем массив для передачи в отображение
                                    $manufs_map_adds[] = array(
                                        'id' => $add_map_id,
                                        'manuf_id' => $manuf['id'],
                                        'manuf_name' => $manuf['name'],
                                        'prov_manuf_id' => $provider_manuf['id'],
                                        'prov_manuf_name' => $provider_manuf['name'],
                                        'provider_name' => $provider['name']
                                    );
                                    break;
                                }
                            }
                            // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем нового вендора в
                            // эталонную базу
                            if ($flag_name == 0) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $manufs_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_manuf_id' => $provider_manuf['id'],
                                    'prov_manuf_name' => $provider_manuf['name'],
                                    'prov_manuf_descrip' => $provider_manuf['description'],
                                    'prov_manuf_image' => $provider_manuf['image']
                                );
                            }
                        } else {
                            // формируем массив для передачи в отображение т.к. добавляться новые значения
                            // пока будут только вручную
                            $manufs_to_add[] = array(
                                'provider_id' => $provider['id'],
                                'provider_name' => $provider['name'],
                                'prov_manuf_id' => $provider_manuf['id'],
                                'prov_manuf_name' => $provider_manuf['name'],
                                'prov_manuf_descrip' => $provider_manuf['description'],
                                'prov_manuf_image' => $provider_manuf['image']
                            );
                        }

                    }
                }
            }

        }

        // Возвращаем полученные данные
        $data = array();
        $data['manufs_map_adds'] = $manufs_map_adds;
        $data['manufs_to_add'] = $manufs_to_add;
        return $data;
    }

    public function updateAttribGtoup() {
        // Метод обновления групп аттрибутов
        // Возвращает массив данных $data с ключами:
        // - attrib_groups_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - attrib_groups_to_add - массив вендоров для добавления в эталонную базу

        $db = new Db;
        $attrib_groups_to_add = array();
        $attrib_groups_map_adds = array();

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


        // Получаем карту сопоставлений по группам аттрибутов
        $maps = $db->getMaps('attribute_group');
        //Собираем id групп аттрибутов поставщиков сопоставленных с нашей эталонной базой
        $map_attrib_groups_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_attrib_groups_id[] = $map['provider_id'];
                }
            }
        }

        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $provider_attrib_groups = $db->getProviderAttributeGroups($provider['id']);
                $attrib_groups = $db->getAttributeGroups();
                foreach ($provider_attrib_groups as $provider_attrib_group) {
                    if (!in_array($provider_attrib_group['id'], $map_attrib_groups_id)) {
                        //
                        if ($attrib_groups != null) {
                            $flag_name = 0;
                            $flag_group = 0;
                            foreach ($attrib_groups as $attrib_group) {
                                if (strcasecmp($attrib_group['name'], $provider_attrib_group['name']) == 0) {
                                    // если имя вендора из таблицы поставщиков равно имени эталонного вендора, то
                                    $flag_name = 1;
                                    // проверяем родительскую группу
                                    // получаем id родительской группы
                                    if ($provider_attrib_group['parent_id'] == 0) {
                                        $our_attrib_group_id = 0;
                                    } else {
                                        $our_attrib_group_id = $db->getMapByProvItemId('attribute_group', $provider_attrib_group['parent_id']);
                                    }

                                    if ($attrib_group['parent_id'] == $our_attrib_group_id) {
                                        $flag_group = 1;
                                        // необходимо его сопоставить
                                        $add_map_id = $db->addMap('attribute_group', $attrib_group['id'], $provider_attrib_group['id']);
                                        // Добавляем запись в детальный лог
                                        $db->addDetailLog('PRICE', '0', 'ADD_MAP_ATTRIB_GROUP', $attrib_group['name'], $provider_attrib_group['name']);
                                        // формируем массив для передачи в отображение
                                        $attrib_groups_map_adds[] = array(
                                            'id' => $add_map_id,
                                            'attrib_group_id' => $attrib_group['id'],
                                            'attrib_group_name' => $attrib_group['name'],
                                            'prov_attrib_group_id' => $provider_attrib_group['id'],
                                            'prov_attrib_group_name' => $provider_attrib_group['name'],
                                            'provider_name' => $provider['name']
                                        );
                                        break;
                                    }
                                }
                            }
                            // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем новую группу
                            // аттрибутов в эталонную базу
                            if (($flag_name == 0) OR ($flag_group == 0)) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $attrib_groups_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_attrib_group_id' => $provider_attrib_group['id'],
                                    'prov_attrib_group_name' => $provider_attrib_group['name'],
                                    'prov_attrib_group_parent_id' => $provider_attrib_group['parent_id']
                                );
                            }
                        } else {
                            // формируем массив для передачи в отображение т.к. добавляться новые значения
                            // пока будут только вручную
                            $attrib_groups_to_add[] = array(
                                'provider_id' => $provider['id'],
                                'provider_name' => $provider['name'],
                                'prov_attrib_group_id' => $provider_attrib_group['id'],
                                'prov_attrib_group_name' => $provider_attrib_group['name'],
                                'prov_attrib_group_parent_id' => $provider_attrib_group['parent_id']
                            );
                        }
                    }
                }
            }

        }

        // Возвращаем полученные данные
        $data = array();
        $data['attrib_groups_map_adds'] = $attrib_groups_map_adds;
        $data['attrib_groups_to_add'] = $attrib_groups_to_add;
        return $data;
    }

    public function updateAttrib() {
        // Метод обновления аттрибутов
        // Возвращает массив данных $data с ключами:
        // - attribs_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - attribs_to_add - массив вендоров для добавления в эталонную базу
        $data = array();

        $db = new Db;
        $attribs_to_add = array();
        $attribs_map_adds = array();
        $attribs_check_to_add = array();

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


        // Получаем карту сопоставлений по аттрибутам
        $maps = $db->getMaps('attribute');
        //Собираем id аттрибутов поставщиков сопоставленных с нашей эталонной базой
        $map_attribs_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_attribs_id[] = $map['provider_id'];
                }
            }
        }

        // Делаем проверку на сопоставление групп аттрибутов, прежде чем начать
        // проверку самих аттрибутов, т.к. это базовые данные
        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $flag = $db->checkMapProviderAttributeGroups($provider['id']);
                if ($flag) {
                    continue;
                } else {
                    $data['warning'][] = 'Необходимо обновить группы аттрибутов по поставщику id: ' . $provider['id'];
                }
            }
        }

        if (!isset($data['warning'])) {
            foreach ($providers as $provider) {
                if ($provider['parent_id'] == null) {
                    $provider_attribs = $db->getProviderAttributes($provider['id']);
                    $attribs = $db->getAttributes();
                    foreach ($provider_attribs as $provider_attrib) {
                        if (!in_array($provider_attrib['id'], $map_attribs_id)) {
                            //
                            if ($attribs != null) {
                                $flag_name = 0;
                                $flag_group = 0;
                                foreach ($attribs as $attrib) {
                                    if (strcasecmp($attrib['name'], $provider_attrib['name']) == 0) {
                                        $flag_name = 1;
                                        $map_prov_attrib_group_id = $db->getMapByProvItemId('attribute_group', $provider_attrib['group_id']);
                                        if ($attrib['group_id'] == $map_prov_attrib_group_id) {
                                            $flag_group = 1;
                                            // если имя аттрибут из таблицы поставщиков равно имени эталонного аттрибута, и
                                            // группы аттрибутов тоже равны то необходимо его сопоставить
                                            $add_map_id = $db->addMap('attribute', $attrib['id'], $provider_attrib['id']);
                                            //$flag_name = 1;
                                            // Добавляем запись в детальный лог
                                            $db->addDetailLog('PRICE', '0', 'ADD_MAP_ATTRIBUTE', $attrib['name'], $provider_attrib['name']);
                                            // формируем массив для передачи в отображение
                                            $attribs_map_adds[] = array(
                                                'id' => $add_map_id,
                                                'attrib_id' => $attrib['id'],
                                                'attrib_name' => $attrib['name'],
                                                'prov_attrib_id' => $provider_attrib['id'],
                                                'prov_attrib_name' => $provider_attrib['name'],
                                                'provider_name' => $provider['name']
                                            );
                                            break;
                                        }
                                    }
                                }
                                // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем новую группу
                                // аттрибутов в эталонную базу
                                if (($flag_name == 0) OR ($flag_group == 0)) {
                                    // формируем массив для передачи в отображение т.к. добавляться новые значения
                                    // пока будут только вручную
                                    $attribs_to_add[] = array(
                                        'provider_id' => $provider['id'],
                                        'provider_name' => $provider['name'],
                                        'prov_attrib_id' => $provider_attrib['id'],
                                        'prov_attrib_name' => $provider_attrib['name'],
                                        'prov_attrib_group_id' => $provider_attrib['group_id']
                                    );
                                }

                            } else {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $attribs_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_attrib_id' => $provider_attrib['id'],
                                    'prov_attrib_name' => $provider_attrib['name'],
                                    'prov_attrib_group_id' => $provider_attrib['group_id']
                                );
                            }
                        }
                    }
                }

            }
        }

        // Возвращаем полученные данные
        $data['attribs_map_adds'] = $attribs_map_adds;
        $data['attribs_to_add'] = $attribs_to_add;
        //$data['attribs_check_to_add'] = $attribs_check_to_add;
        return $data;
    }

    public function updateAttribValues() {
        // Метод обновления значений аттрибутов
        // Возвращает массив данных $data с ключами:
        // - attrib_values_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - attrib_values_to_add - массив вендоров для добавления в эталонную базу
        $data = array();

        $db = new Db;
        $attrib_values_to_add = array();
        $attrib_values_map_adds = array();


        // Получаем список всех поставщиков
        $providers = $db->getProviders();


        // Получаем карту сопоставлений по аттрибутам
        $maps = $db->getMaps('attribute_value');
        //Собираем id аттрибутов поставщиков сопоставленных с нашей эталонной базой
        $map_attrib_values_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_attrib_values_id[] = $map['provider_id'];
                }
            }
        }

        // Делаем проверку на сопоставление групп аттрибутов, прежде чем начать
        // проверку самих аттрибутов, т.к. это базовые данные
        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $flag = $db->checkMapProviderAttributeGroups($provider['id']);
                if ($flag) {
                    continue;
                } else {
                    $data['warning'][] = 'Необходимо обновить группы аттрибутов по поставщику id: ' . $provider['id'];
                }
            }
        }

        // Делаем проверку на сопоставление аттрибутов, прежде чем начать
        // проверку самих аттрибутов, т.к. это базовые данные
        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $flag = $db->checkMapProviderAttributes($provider['id']);
                if ($flag) {
                    continue;
                } else {
                    $data['warning'][] = 'Необходимо обновить аттрибуты по поставщику id: ' . $provider['id'];
                }
            }
        }

        if (!isset($data['warning'])) {
            foreach ($providers as $provider) {
                if ($provider['parent_id'] == null) {
                    $provider_attributes = $db->getProviderAttributes($provider['id']);
                    foreach ($provider_attributes as $provider_attribute) {
                        $provider_attribute_values = $db->getProviderAttributeValues($provider['id'], $provider_attribute['id']);
                        foreach ($provider_attribute_values as $provider_attribute_value) {
                            $our_attrib_value_id = $db->getMapByProvItemId('attribute_value', $provider_attribute_value['id']);
                            if ($our_attrib_value_id) {
                                continue;
                            } else {
                                // Если значение аттрибута не сопоставлено ни с одним значением из эталонной базы,
                                // то нужно попробовать его сопоставить, а если не получится, то нужно его записать в
                                // массив значений аттрибутов для добавления
                                $add_map_attrib_value_id = false;
                                $our_attribute_id = $db->getMapByProvItemId('attribute', $provider_attribute['id']);
                                $attribute_values = $db->getAttributeValues($our_attribute_id);
                                if ($attribute_values == null) {
                                    $attribute_values = array();
                                }
                                foreach ($attribute_values as $attribute_value) {
                                    if ($attribute_value['value'] == $provider_attribute_value['value']) {
                                        // Если аттрибуты равны по значению, то необходимо их сопоставить
                                        $add_map_attrib_value_id = $db->addMap('attribute_value', $attribute_value['id'], $provider_attribute_value['id']);
                                        // Далее необходимо добавить запись в массив сопоставленных (добавленных) карт
                                        $db->addDetailLog('PRICE', '0', 'ADD_MAP_ATTRIB_VALUE', $attribute_value['id'], $provider_attribute_value['id']);
                                        // формируем массив для передачи в отображение
                                        $attrib_values_map_adds[] = array(
                                            'id' => $add_map_attrib_value_id,
                                            'attrib_value_id' => $attribute_value['id'],
                                            'attrib_value_value' => $attribute_value['value'],
                                            'prov_attrib_value_id' => $provider_attribute_value['id'],
                                            'prov_attrib_value_value' => $provider_attribute_value['value'],
                                            'provider_name' => $provider['name']
                                        );
                                        break;
                                    }
                                }
                                if ($add_map_attrib_value_id == false) {
                                    // Если после перебора выше не удалось сопоставить значение аттрибута поставщика
                                    // со значением аттрибута из эталонной базы, то его необходимо добавить в массив
                                    // значения аттрибутов для добавления для передачи в отображение
                                    $our_attribute = $db->getAttribute($our_attribute_id);
                                    $attrib_values_to_add[] = array(
                                        'attrib_id' => $our_attribute['id'],
                                        'attrib_name' => $our_attribute['name'],
                                        'prov_attrib_id' => $provider_attribute['id'],
                                        'prov_attrib_name' => $provider_attribute['name'],
                                        'prov_attrib_value_id' => $provider_attribute_value['id'],
                                        'prov_attrib_value' => $provider_attribute_value['value'],
                                        'provider_id' => $provider['id'],
                                        'provider_name' => $provider['name']
                                    );

                                }


                            }


                        }

                    }


                }

            }
        }

        // Возвращаем полученные данные
        $data['attrib_values_map_adds'] = $attrib_values_map_adds;
        $data['attrib_values_to_add'] = $attrib_values_to_add;
        //$data['attribs_check_to_add'] = $attribs_check_to_add;
        return $data;
    }

    public function updateCategories() {
        // Метод обновления категорий
        // Возвращает массив данных $data с ключами:
        // - categories_map_adds - массив добавленных автоматически карт сопоставления по вендорам
        // - categories_to_add - массив вендоров для добавления в эталонную базу

        $db = new Db;
        $categories_to_add = array();
        $categories_map_adds = array();

        // Получаем список всех поставщиков
        $providers = $db->getProviders();


        // Получаем карту сопоставлений по категориям
        $maps = $db->getMaps('category');
        //Собираем id категорий поставщиков сопоставленных с нашей эталонной базой
        $map_categories_id = array();
        if ($maps !== false) {
            if ($maps !== null) {
                foreach ($maps as $map) {
                    $map_categories_id[] = $map['provider_id'];
                }
            }
        }

        foreach ($providers as $provider) {
            if ($provider['parent_id'] == null) {
                $provider_categories = $db->getProviderCategories($provider['id']);
                $categories = $db->getCategories();
                foreach ($provider_categories as $provider_category) {
                    if (!in_array($provider_category['id'], $map_categories_id)) {
                        //
                        if ($categories != null) {
                            $flag_name = 0;
                            $flag_parent = 0;
                            foreach ($categories as $category) {
                                if (strcasecmp($category['name'], $provider_category['name']) == 0) {
                                    // если имя вендора из таблицы поставщиков равно имени эталонного вендора, то
                                    $flag_name = 1;
                                    // проверяем родительскую группу
                                    // получаем id родительской группы
                                    if (($provider_category['provider_parent_id'] == 0) OR ($provider_category['provider_parent_id'] == '') OR ($provider_category['provider_parent_id'] == null)) {
                                        $our_category_parent_id = 0;
                                    } else {
                                        $our_prov_category_id = $db->getCatIdByProvCatId($provider['id'], $provider_category['provider_parent_id']);
                                        $our_prov_category_parent_id = $db->getMapByProvItemId('category', $our_prov_category_id);
                                    }

                                    if ($category['parent_id'] == $our_prov_category_parent_id) {
                                        $flag_parent = 1;
                                        // необходимо его сопоставить
                                        $add_map_id = $db->addMap('category', $category['id'], $our_prov_category_parent_id);
                                        // Добавляем запись в детальный лог
                                        $db->addDetailLog('PRICE', '0', 'ADD_MAP_CATEGORY', $category['name'], $provider_category['name']);
                                        // формируем массив для передачи в отображение
                                        $categories_map_adds[] = array(
                                            'id' => $add_map_id,
                                            'category_id' => $category['id'],
                                            'category_name' => $category['name'],
                                            'prov_category_id' => $provider_category['id'],
                                            'prov_category_name' => $provider_category['name'],
                                            'provider_name' => $provider['name']
                                        );
                                        break;
                                    }
                                }
                            }
                            // Проверяем, удалось ли найти сопоставление. Если нет, то добавляем новую категорию
                            // в эталонную базу
                            if (($flag_name == 0) OR ($flag_parent == 0)) {
                                // формируем массив для передачи в отображение т.к. добавляться новые значения
                                // пока будут только вручную
                                $categories_to_add[] = array(
                                    'provider_id' => $provider['id'],
                                    'provider_name' => $provider['name'],
                                    'prov_category_id' => $provider_category['id'],
                                    'prov_category_name' => $provider_category['name'],
                                    'prov_category_description' => $provider_category['description'],
                                    'prov_category_image' => $provider_category['image'],
                                    'prov_category_parent_id' => $provider_category['provider_parent_id'],
                                    'prov_category_parent_name' => $provider_category['provider_parent_cat_name']
                                );
                            }
                        } else {
                            // формируем массив для передачи в отображение т.к. добавляться новые значения
                            // пока будут только вручную
                            $categories_to_add[] = array(
                                'provider_id' => $provider['id'],
                                'provider_name' => $provider['name'],
                                'prov_category_id' => $provider_category['id'],
                                'prov_category_name' => $provider_category['name'],
                                'prov_category_description' => $provider_category['description'],
                                'prov_category_image' => $provider_category['image'],
                                'prov_category_parent_id' => $provider_category['provider_parent_id'],
                                'prov_category_parent_name' => $provider_category['provider_parent_cat_name']
                            );
                        }
                    }
                }
            }

        }

        // Возвращаем полученные данные
        $data = array();
        $data['categories_map_adds'] = $categories_map_adds;
        $data['categories_to_add'] = $categories_to_add;
        return $data;
    }

}
