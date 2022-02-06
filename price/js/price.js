$(document).delegate('.link_add_model', 'click', function() {
    var modelName = $(this).data('model-name');
    var provModelId = $(this).data('prov-model-id');
    var provName = $(this).data('prov-name');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_model_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'GET',
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
        type: 'GET',
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
        type: 'GET',
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
    var provAttribGroupParentId = $(this).data('prov-attrib-group-parent-id');
    var rowId = '#' + $(this).data('row-id');
    var oper = 'add_attrib_group_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'GET',
        data: {
            operation: oper,
            attrib_group_name: attribGroupName,
            prov_manuf_id: provAttribGroupId,
            attrib_group_parent_id: attribGroupParentId
        },
        dataType: 'json',
        success: function(json) {
            $('#tableMaps tr:last').after('<tr><td></td><td>' + json['map_id'] + '</td><td>' +
                attribGroupName + '</td><td>' + json['attrib_group_id'] + '</td><td>' + attribGroupName + '</td><td>'+
                provAttribGroupId + '</td><td>' + provName +'</td></tr>');
            $(rowId).remove();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).delegate('.link_add_attrib_group_all', 'click', function() {
    var attribsToAdd = [];
    $('#tableAttribGroups tbody>tr').each(function () {
        $this = $(this);
        var provId = $this.find('.prov-id').text();
        var attribGroupId = $this.find('.attrib-group-id').text();
        var attribGroupName = $this.find('.attrib-group-name').text();
        var provAttribGroupParentId = $this.find('.prov-attrib-group-parent-id').text();
        attribsToAdd.push({
            provider_id: provId,
            prov_attrib_group_id: attribGroupId,
            prov_attrib_group_name: attribGroupName,
            prov_attrib_group_parent_id: provAttribGroupParentId
        })
        }
    );


});