/**  public/js/language-form.js */

var fail = function(response){
    var message, json = response.responseJSON;
    if (json && json.error && json.error.message) {
        message = json.error.message;
    } else {
        message = "Sorry -- there's been an error that prevented us from completing this operation.";
    }
    if (! $('#error-message').length) {
        $('<div/>')
            .addClass("alert alert-warning shadow-sm border")
            .attr({id:"error-message"}).html(message)
            .insertBefore($("form").first();
    } else {
        $('#error-message').html(message);
    }
};

$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this language?")) {
            return;
        }
        var name = $('form#language').data('language_name');
        var url = $('form#language').data('redirect_url');
        var id = $('input[name="id"]').val();
        $.post('/admin/languages/delete/'+id,{name:name})
            .done(function(response){document.location = url;})
            .fail(fail);
        });
});
