/**  public/js/language-form.js */
$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this language?")) {
            return;
        }
        var name = $('form#language').data('language_name');
        var url = $('form#language').data('redirect_url');
        var id = $('input[name="id"]').val();
        $.post('/admin/languages/delete/'+id,{name:name},function(response){
            if (response.redirect) {
                // back to index page
                //document.location = url;
            } else {
                // stay here and display error
                var error = response.error.message;
                if (! $('#failed_deletion_error').length) {
                    $('<div/>')
                        .addClass("alert alert-warning")
                        .attr({id:"failed_deletion_error"}).html(error)
                        .insertBefore($('form#language'));
                } else {
                    $('#failed_deletion_error').html(error);
                }
            }
        },'json');
    });
});
