/**  public/js/event-types-form.js */
$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this event-type?")) {
            return;
        }
        var button = $(this);
        var data = $('form#event-type').data();
        var name = data.eventType_name
        var url = data.redirect_url;
        var id = $('input[name="id"]').val();

        $.post('/admin/event-types/delete/'+id,{name:name},function(response){
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
                        .insertBefore($('form#event-type'));
                } else {
                    $('#failed_deletion_error').html(error);
                    button.addClass("disabled").attr({
                        "aria-disabled":true,disabled:"disabled",
                        title:'this database record cannot be deleted'}
                    );
                }
            }
        },'json');
    });
});
