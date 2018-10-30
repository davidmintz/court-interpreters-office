/** public/js/defendant-form.js */

const is_literal_duplicate = (entity) => {
    return entity.surnames === $("#surnames").val().trim() &&
    entity.given_names === $("#given_names").val().trim();
};
$(function(){
    var form = $('#defendant-form');
    $('#btn-submit').on("click",function(event){
        event.preventDefault();
        var data = form.serialize();
        var action = $("input[name=id]").val() ? "update":"insert";
        $.post(form.attr('action'),data,function(response){
            if (response.validation_errors) {
                return displayValidationErrors(response.validation_errors);
            } else {
                $('.validation-error').hide();
            }
            if (response.duplicate_entry_error) {
                var existing = response.existing_entity;
                if (is_literal_duplicate(existing)) {
                    if (action === "insert") {
                        $("#error-div h3").text("error: duplicate entry");
                        $("#error-message").text(
                            "This name is already in your database"
                        ).parent().show();
                    } else {
                        // inexact. they should edit, not insert
                        msg = "";
                    }

                } else {

                }

                form.prepend($('<input>').attr({type:'hidden',name:'duplicate_resolution_required',value:1}));
                $('#deft-existing-duplicate-name').text(existing);
                var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
                console.warn("daFUQ?");
                return $(shit).show();
            }
            // if (response.status == 'error') {
            //     fail(fail)
            // }
            document.location = form.data('redirect_url');

        },'json').fail(fail);
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
