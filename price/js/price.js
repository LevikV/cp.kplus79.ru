$(document).delegate('#button-add-model', 'click', function() {
    $.ajax({
        url: 'price/oper.php',
        type: 'GET',
        data: {operation:'add_model'},
        complete: function() {
            $('#button-add-org').button('reset');
        },
        success: function(data) {
            alert(data);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });


});