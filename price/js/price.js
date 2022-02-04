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