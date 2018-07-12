/**  public/js/reset-password.js */

var displayValidationErrors, $;
$(function(){
    $("#btn-submit").on("click",function(event){
        event.preventDefault();
        var data = $('#form-reset-password').serialize();
        $.post(document.location.href,data)
        .then(function(response){
            if (response.validation_errors && response.validation_errors.length)
            {
                return displayValidationErrors(response.validation_errors);
            }
            $("#welcome").remove();
            $("#success").show();
        });
    });
});
