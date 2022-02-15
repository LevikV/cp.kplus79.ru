<?php
// Данный модуль нужен для выполнения фоновых операций по работе с БД
// принимает в качестве аргумента (operation) значение кода операции

// Включаем автоподгрузку классов
spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
});
// Загружаем глобальные настройки
require_once($_SERVER['DOCUMENT_ROOT'] . '/price/system/config.php');
// Объявляем глобальный массив ошибок
$ERROR = array();

// Проверяем передан ли код операции для выполнения
if (isset($_POST['operation'])) {
    // Действие добавления новой модели в эталонную базу
    // возвращает результат в JSON формате, в результате id добавленной модели и id карты сопоставления
    if ($_POST['operation'] == 'add_model_from_prov') {
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['model_name'];
        // добавляем модель
        $model_id = $db->addModel($data);
        if ($model_id) {
            $json['model_id'] = $model_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('model', $model_id, $_POST['prov_model_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты модели';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении модели в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_manuf_from_prov') {
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['manuf_name'];
        // добавляем производителя
        $manuf_id = $db->addManufacturer($data);
        if ($manuf_id) {
            $json['manuf_id'] = $manuf_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('manufacturer', $manuf_id, $_POST['prov_manuf_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты производителя';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении производителя в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_vendor_from_prov') {
        //******** Операция добавления вендора ********
        $db = new Db;
        // формируем данные
        $data = array();
        $data['name'] = $_POST['vendor_name'];
        // добавляем вендора
        $vendor_id = $db->addVendor($data);
        if ($vendor_id) {
            $json['vendor_id'] = $vendor_id;
            // добавляем карту сопоставления
            $map_id = $db->addMap('vendor', $vendor_id, $_POST['prov_vendor_id']);
            if ($map_id) {
                $json['map_id'] = $map_id;
            } else {
                $json['error'][] = 'Ошибка при добавлении карты вендора';
            }
        } else {
            $json['error'][] = 'Ошибка при добавлении вендора в эталонную базу.';
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_attrib_group_from_prov') {
        //******** Операция добавления группы аттрибута ********
        $json = array();
        $db = new Db;
        if ($_POST['prov_attrib_group_parent_id'] == 0) {
            $data = array();
            $data['name'] = $_POST['prov_attrib_group_name'];
            $attrib_group_id = $db->addAttributeGroup($data);
            if ($attrib_group_id) {
                $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE_GROUP', '', $data['name']);
                $attrib_group_map_id = $db->addMap('attribute_group', $attrib_group_id, $_POST['prov_attrib_group_id']);
                if ($attrib_group_map_id) {
                    $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE_GRPOUP', $attrib_group_id, $_POST['prov_attrib_group_id']);
                    $json['map_id'] = $attrib_group_map_id;
                    $json['attrib_group_id'] = $attrib_group_id;
                }
            }
        } else {
            $our_item_id = $db->getMapByProvItemId('attribute_group', $_POST['prov_attrib_group_parent_id']);
            if ($our_item_id) {
                $data = array();
                $data['name'] = $_POST['prov_attrib_group_name'];
                $data['parent_id'] = $_POST['prov_attrib_group_parent_id'];
                $attrib_group_id = $db->addAttributeGroup($data);
                if ($attrib_group_id) {
                    $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE_GROUP', '', $data['name']);
                    $attrib_group_map_id = $db->addMap('attribute_group', $attrib_group_id, $_POST['prov_attrib_group_id']);
                    if ($attrib_group_map_id) {
                        $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE_GRPOUP', $attrib_group_id, $_POST['prov_attrib_group_id']);
                        $json['map_id'] = $attrib_group_map_id;
                        $json['attrib_group_id'] = $attrib_group_id;
                    }
                }
            } else {
                $json['warning'] = 'Группа не может быть добавлена, т.к. еще не создана родительская группа!';
            }
        }
        echo json_encode($json);
    } elseif ($_POST['operation'] == 'add_attrib_group_from_prov_all') {
        //******** Операция добавления разом всех групп аттрибутов ********
        // Операция добавления всех групп аттрибутов
        $attrib_groups_map_adds = array();
        $db = new Db;
        // формируем данные
        $attrib_groups_to_add = array();
        $attrib_groups_to_add = $_POST['attrib_groups_to_add'];

        $providers = array();
        foreach ($attrib_groups_to_add as $attrib_group) {
            if (!in_array($attrib_group['provider_id'], $providers, true)) {
                $providers[] = $attrib_group['provider_id'];
            }
        }

        foreach ($providers as $provider) {
            $temp = array();
            foreach ($attrib_groups_to_add as $attrib_group) {
                // Перебираем все аттрибуты по текущему провайдеру
                if ($attrib_group['provider_id'] == $provider) {
                    if ($attrib_group['prov_attrib_group_parent_id'] == 0) {
                        $data = array();
                        $data['name'] = $attrib_group['prov_attrib_group_name'];
                        $attrib_group_id = $db->addAttributeGroup($data);
                        if ($attrib_group_id) {
                            $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE_GROUP', '', $data['name']);
                            $attrib_group_map_id = $db->addMap('attribute_group', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                            if ($attrib_group_map_id) {
                                $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE_GRPOUP', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                                $provider_data = $db->getProvider($provider);
                                $attrib_groups_map_adds[] = array(
                                    'id' => $attrib_group_map_id,
                                    'attrib_group_id' => $attrib_group_id,
                                    'attrib_group_name' => $data['name'],
                                    'prov_attrib_group_id' => $attrib_group['prov_attrib_group_id'],
                                    'prov_attrib_group_name' => $attrib_group['prov_attrib_group_name'],
                                    'provider_name' => $provider_data['name']
                                );
                            }
                        }
                    } else {
                        $map = $db->getMapByProvItemId('attribute_group', $attrib_group['prov_attrib_group_parent_id']);
                        if ($map === null) {
                            $temp[] = $attrib_group;
                        } elseif ($map != false) {
                            $data = array();
                            $data['name'] = $attrib_group['prov_attrib_group_name'];
                            $data['parent_id'] = $map;
                            $attrib_group_id = $db->addAttributeGroup($data);
                            if ($attrib_group_id) {
                                $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE_GROUP', '', $data['name']);
                                $attrib_group_map_id = $db->addMap('attribute_group', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                                if ($attrib_group_map_id) {
                                    $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE_GRPOUP', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                                    $provider_data = $db->getProvider($provider);
                                    $attrib_groups_map_adds[] = array(
                                        'id' => $attrib_group_map_id,
                                        'attrib_group_id' => $attrib_group_id,
                                        'attrib_group_name' => $data['name'],
                                        'prov_attrib_group_id' => $attrib_group['prov_attrib_group_id'],
                                        'prov_attrib_group_name' => $attrib_group['prov_attrib_group_name'],
                                        'provider_name' => $provider_data['name']
                                    );
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($temp)) {
                while (!empty($temp)) {
                    foreach ($temp as $key => $attrib_group) {
                        $map = $db->getMapByProvItemId('attribute_group', $attrib_group['prov_attrib_group_parent_id']);
                        if ($map) {
                            $data = array();
                            $data['name'] = $attrib_group['prov_attrib_group_name'];
                            $data['parent_id'] = $map;
                            $attrib_group_id = $db->addAttributeGroup($data);
                            if ($attrib_group_id) {
                                $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE_GROUP', '', $data['name']);
                                $attrib_group_map_id = $db->addMap('attribute_group', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                                if ($attrib_group_map_id) {
                                    $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE_GRPOUP', $attrib_group_id, $attrib_group['prov_attrib_group_id']);
                                    $provider_data = $db->getProvider($provider);
                                    $attrib_groups_map_adds[] = array(
                                        'id' => $attrib_group_map_id,
                                        'attrib_group_id' => $attrib_group_id,
                                        'attrib_group_name' => $data['name'],
                                        'prov_attrib_group_id' => $attrib_group['prov_attrib_group_id'],
                                        'prov_attrib_group_name' => $attrib_group['prov_attrib_group_name'],
                                        'provider_name' => $provider_data['name']
                                    );
                                    // Если все добавилось успешно, то удаляем из массива группу аттрибутов
                                    unset($temp[$key]);

                                }
                            }
                        }
                        //
                    }
                    //
                }
            }
        }
        echo json_encode($attrib_groups_map_adds);
    } elseif ($_POST['operation'] == 'add_model_from_prov_all') {
        //******** Операция добавления всех моделей разом ********
        // Операция добавления всех моделей
        $models_map_adds = array();
        $db = new Db;
        // формируем данные
        $models_to_add = array();
        $models_to_add = $_POST['models_to_add'];
        //
        foreach ($models_to_add as $model_to_add) {
            $data = array();
            $data['name'] = $model_to_add['prov_model_name'];
            $model_add_id = $db->addModel($data);
            if ($model_add_id) {
                $db->addDetailLog('OPER', 0, 'ADD_MODEL', '', $data['name']);
                $model_map_add_id = $db->addMap('model', $model_add_id, $model_to_add['prov_model_id']);
                if ($model_map_add_id) {
                    $db->addDetailLog('OPER', 0, 'ADD_MAP_MODEL', $model_add_id, $model_to_add['prov_model_id']);
                    $provider_data = $db->getProvider($model_to_add['provider_id']);
                    $models_map_adds[] = array(
                        'id' => $model_map_add_id,
                        'model_id' => $model_add_id,
                        'model_name' => $data['name'],
                        'prov_model_id' => $model_to_add['prov_model_id'],
                        'prov_model_name' => $model_to_add['prov_model_name'],
                        'provider_name' => $provider_data['name']
                    );
                }
            }
        }
        echo json_encode($models_map_adds);
    } elseif ($_POST['operation'] == 'add_attrib_from_prov') {
        //******** Операция добавления аттрибута ********
        $json = array();
        $db = new Db;
        if (!isset($_POST['prov_attrib_group_id']) OR !isset($_POST['prov_attrib_name'])) {
            $json['error'] = 'Ошибка передачи параметров для добавления аттрибута!';
        }
        if (!isset($json['error'])) {
            $attrib_group_id = $db->getMapByProvItemId('attribute_group', $_POST['prov_attrib_group_id']);
            $data = array();
            $data['name'] = $_POST['prov_attrib_name'];
            $data['group_id'] = $attrib_group_id;
            $our_attrib_id = $db->addAttribute($data);
            if ($our_attrib_id) {
                $db->addDetailLog('OPER', 0, 'ADD_ATTRIBUTE', '', $data['name']);
                $attrib_map_add_id = $db->addMap('attribute', $our_attrib_id, $_POST['prov_attrib_id']);
                if ($attrib_map_add_id) {
                    $db->addDetailLog('OPER', 0, 'ADD_MAP_ATTRIBUTE', $our_attrib_id, $_POST['prov_attrib_id']);
                    $json['map_id'] = $attrib_map_add_id;
                    $json['attrib_id'] = $our_attrib_id;
                }
            }
        }

        echo json_encode($json);
    }
}
