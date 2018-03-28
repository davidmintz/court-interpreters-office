/** public/js/defendant-form.js */

$(function(){

    console.warn("the FUCK????");

    $('#btn-submit').on("click",function(event){
        event.preventDefault();
        var form = $('#defendant-form');
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
            if (response.error) {
                return alert('shit. there was an error: '+response.error);
            }


        },'json');
    });
});
