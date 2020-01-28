/*
global $, fail, displayValidationErrors
*/
$(function(){
    $("form .btn-success").on("click",function(){
        var form = $(this).closest("form");
        $.post(form.attr("action"),form.serialize())
        .then((res)=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            }
            $("#message-success").slideDown();
            $("form input").one("change",()=>$("#message-success").slideUp());
        }).fail(fail);
    });
});
