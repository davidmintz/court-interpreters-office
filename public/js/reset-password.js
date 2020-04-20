/**  public/js/reset-password.js */
/* global displayValidationErrors, $, fail */

$(function(){
    $("[data-toggle=\"popover\"]").popover();
    $("#btn-submit").on("click",function(event){
        event.preventDefault();
        var data = $("#form-reset-password").serialize();
        $.post(document.location.href,data)
            .then(function(response){
                if (response.validation_errors)
                {
                    return displayValidationErrors(response.validation_errors);
                } //else, all good
                $("#welcome").remove();
                $("#success").show();
            }).fail(fail);
    });
});
