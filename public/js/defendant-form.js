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
            if (response.duplicate_entry_error) {
                var existing = response.existing_entity;
                form.prepend($('<input>').attr({type:'hidden',name:'duplicate_resolution_required',value:1}));
                $('#deft-existing-duplicate-name').text(existing);
                var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
                console.warn("daFUQ?");
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
        if (! window.confirm(
            "Are you sure you want to delete this defendant name?")) {
            return;
        }
        var name = form.data('defendant_name');
        var url = form.data('redirect_url')
            ||`${window.basePath || ""}/admin/defendants`;
        var id = $('input[name="id"]').val();
        $.post('/admin/defendants/delete/'+id,{name})
        .done( ()=>window.document.location = url)
        .fail(fail);

    });
});
