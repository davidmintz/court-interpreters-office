/** public/js/defendant-form.js */

$(function(){
    var form = $('#defendant-form');
    $('#btn-submit').on("click",function(event){
        event.preventDefault();
        var data = form.serialize();
        $.post(form.attr('action'),data,function(response){
            if (response.validation_errors) {
                return displayValidationErrors(response.validation_errors);
            } else {
                $('.validation-error').hide();
            }
            if (response.inexact_duplicate_found) {
                var existing = response.existing_entity;
                form.prepend($('<input>').attr({type:'hidden',name:'duplicate_resolution_required',value:1}));
                $('#deft-existing-duplicate-name').text(existing);
                var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
                return $(shit).show();
            }
            if (response.status == 'error') {
                return alert('ah, shit. there was an error: '+response.status.message);
            }
            document.location = form.data('redirect_url');

        },'json');
    });
    form.on("click",'#btn-select-all, #btn-invert-selection',function(event){
        event.preventDefault();
        var checkboxes = $('form input[type=checkbox]');
        if ($(event.target).attr('id')=='btn-select-all') {
            checkboxes.prop("checked",true);
        } else {
            checkboxes.each(function(){
                var checkbox = $(this);
                var checked = checkbox.prop("checked");
                checkbox.prop("checked",!checked);
            });
        }
    });
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this defendant name?")) {
            return;
        }
        var name = form.data('defendant_name');
        var url = form.data('redirect_url');
        var id = $('input[name="id"]').val();
        $.post('/admin/defendants/delete/'+id,{name:name},function(response){
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
                        .insertBefore(form);
                } else {
                    $('#failed_deletion_error').html(error);
                }
            }
        },'json');
    });
});
