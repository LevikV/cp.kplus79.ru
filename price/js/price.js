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