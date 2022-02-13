$(document).delegate('.link_add_model', 'click', function() {
    var modelName = $(this).data('model-name');
    var provModelId = $(this).data('prov-model-id');
    var provName = $(this).data('prov-name');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_model_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            model_name: modelName,
            prov_model_id: provModelId
        },
        dataType: 'json',
        success: function(json) {
            $('#tableMaps tr:last').after('<tr><td></td><td>' + json['map_id'] + '</td><td>' +
                modelName + '</td><td>' + json['model_id'] + '</td><td>' + modelName + '</td><td>'+
                provModelId + '</td><td>' + provName +'</td></tr>');
            $(rowId).remove();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).delegate('.link_add_vendor', 'click', function() {
    var vendorName = $(this).data('vendor-name');
    var provVendorId = $(this).data('prov-vendor-id');
    var provName = $(this).data('prov-name');
    var vendorDescrip = $(this).data('vendor-descrip');
    var vendorImage = $(this).data('vendor-image');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_vendor_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            vendor_name: vendorName,
            prov_vendor_id: provVendorId,
            vendor_descrip: vendorDescrip,
            vendor_image: vendorImage
        },
        dataType: 'json',
        success: function(json) {
            $('#tableMaps tr:last').after('<tr><td></td><td>' + json['map_id'] + '</td><td>' +
                vendorName + '</td><td>' + json['vendor_id'] + '</td><td>' + vendorName + '</td><td>'+
                provVendorId + '</td><td>' + provName +'</td></tr>');
            $(rowId).remove();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).delegate('.link_add_manuf', 'click', function() {
    var manufName = $(this).data('manuf-name');
    var provManufId = $(this).data('prov-manuf-id');
    var provName = $(this).data('prov-name');
    var manufDescrip = $(this).data('manuf-descrip');
    var manufImage = $(this).data('manuf-image');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_manuf_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            manuf_name: manufName,
            prov_manuf_id: provManufId,
            manuf_descrip: manufDescrip,
            manuf_image: manufImage
        },
        dataType: 'json',
        success: function(json) {
            $('#tableMaps tr:last').after('<tr><td></td><td>' + json['map_id'] + '</td><td>' +
                manufName + '</td><td>' + json['manuf_id'] + '</td><td>' + manufName + '</td><td>'+
                provManufId + '</td><td>' + provName +'</td></tr>');
            $(rowId).remove();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).delegate('.link_add_attrib_group', 'click', function() {
    var attribGroupName = $(this).data('attrib-group-name');
    var provAttribGroupId = $(this).data('prov-attrib-group-id');
    var provName = $(this).data('prov-name');
    var provAttribGroupParentId = $(this).data('attrib-group-parent-id');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_attrib_group_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            prov_attrib_group_name: attribGroupName,
            prov_attrib_group_id: provAttribGroupId,
            prov_attrib_group_parent_id: provAttribGroupParentId
        },
        dataType: 'json',
        success: function(json) {
            if (json['warning']) {
                alert(json['warning']);
            } else {
                $('#tableMaps tr:last').after('<tr><td></td><td>' + json['map_id'] + '</td><td>' +
                    attribGroupName + '</td><td>' + json['attrib_group_id'] + '</td><td>' + attribGroupName + '</td><td>'+
                    provAttribGroupId + '</td><td>' + provName +'</td></tr>');
                $(rowId).remove();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).delegate('.link_add_attrib_group_all', 'click', function() {
    var oper = 'add_attrib_group_from_prov_all';
    var attribGroupsToAdd = [];
    $this_link = $(this);
    $('#tableAttribGroups tbody>tr').each(function () {
        $this = $(this);
        var provId = $this.find('.prov-id').text();
        var attribGroupId = $this.find('.attrib-group-id').text();
        var attribGroupName = $this.find('.attrib-group-name').text();
        var provAttribGroupParentId = $this.find('.prov-attrib-group-parent-id').text();
        attribGroupsToAdd.push({
            provider_id: provId,
            prov_attrib_group_id: attribGroupId,
            prov_attrib_group_name: attribGroupName,
            prov_attrib_group_parent_id: provAttribGroupParentId
        })
    });
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            attrib_groups_to_add: attribGroupsToAdd
        },
        dataType: 'json',
        success: function(json) {
            if (json['error']) {
                alert('Какая то ошибка произошла...!');
            } else {
                $.each(json, function (key, data) {
                    $('#tableMaps tr:last').after('<tr><td></td><td>' + data['id'] + '</td><td>' +
                        data['attrib_group_name'] + '</td><td>' + data['attrib_group_id'] + '</td><td>' + data['prov_attrib_group_name'] + '</td><td>'+
                        data['prov_attrib_group_id'] + '</td><td>' + data['provider_name'] +'</td></tr>');
                });
                $('#tableAttribGroups tbody>tr').each(function () {
                    $(this).remove();
                });
                $this_link.remove();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });

});

$(document).delegate('.link_add_model_all', 'click', function() {
    var oper = 'add_model_from_prov_all';
    var modelsToAdd = [];
    $this_link = $(this);
    $('#tableModels tbody>tr').each(function () {
        $this = $(this);
        var provId = $this.find('.prov-id').text();
        var modelId = $this.find('.model-id').text();
        var modelName = $this.find('.model-name').text();
        modelsToAdd.push({
            provider_id: provId,
            prov_model_id: modelId,
            prov_model_name: modelName
        })
    });
    $.ajax({
        url: 'price/oper.php',
        type: 'POST',
        data: {
            operation: oper,
            models_to_add: modelsToAdd
        },
        dataType: 'json',
        success: function(json) {
            if (json['error']) {
                alert('Какая то ошибка произошла...!');
            } else {
                $.each(json, function (key, data) {
                    $('#tableMaps tr:last').after('<tr><td></td><td>' + data['id'] + '</td><td>' +
                        data['model_name'] + '</td><td>' + data['model_id'] + '</td><td>' + data['prov_model_name'] + '</td><td>'+
                        data['prov_model_id'] + '</td><td>' + data['provider_name'] +'</td></tr>');
                });
                $('#tableModels tbody>tr').each(function () {
                    $(this).remove();
                });
                $this_link.remove();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});