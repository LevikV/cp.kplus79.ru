$(document).delegate('.link_add_model', 'click', function() {
    var modelName = $(this).data('model-name');
    var provModelId = $(this).data('prov-model-id');
    var oper = 'add_model_from_prov';
    $.ajax({
        url: 'price/oper.php',
        type: 'GET',
        data: {
            operation: oper,
            model_name: modelName,
            prov_model_id: provModelId
        },
        success: function(data) {
            alert(data);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });


});