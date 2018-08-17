var $;

$(function(){

    var name = $("#lastname").val();
    if ($("#firstname").val()) {
        name += ", "+ $("#firstname").val();
    }
    var id = $('input[name="person[id]"]').val();

    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this person?")) {
            return;
        }
        $.post('/admin/people/delete/'+id,{name:name},function(response){
            if (response.redirect) {
                // back to index page
                document.location = document.referrer || (window.basePath + '/admin/people');
            } else {
                // stay here and display error
                var error = response.error.message;
                if (! $('#failed_deletion_error').length) {
                    $('<div/>')
                        .addClass("alert alert-warning")
                        .attr({id:"failed_deletion_error"}).html(error)
                        .insertBefore($('form#person-form'));
                } else {
                    $('#failed_deletion_error').html(error);
                }
            }
        },'json');
    });
});
