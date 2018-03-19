/**  public/js/locations-form.js */
$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this location?")) {
            return;
        }
        var name = $('form#location').data('location_name');
        var url = $('form#location').data('redirect_url');
        var id = $('input[name="id"]').val();
        $.post('/admin/locations/delete/'+id,{name:name},function(response){
            if (response.redirect) {
                // back to index page
                document.location = url;
            } else {
                // stay here and display error
                var error = response.error.message;
                if (! $('#failed_deletion_error').length) {
                    $('<div/>')
                        .addClass("alert alert-warning")
                        .attr({id:"failed_deletion_error"}).html(error)
                        .insertBefore($('form#location'));
                } else {
                    $('#failed_deletion_error').html(error);
                }
            }
        },'json');
    });
});
