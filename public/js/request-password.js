    
/* global displayValidationErrors, $ */
$(function(){
    $("#btn-submit").on("click",function(){

        $.post( "/user/request-password",{
            email:$("#email").val(),
            csrf :$("input[name=csrf]").val()
        }).then(function(response){
            if (response.validation_errors) {
                return displayValidationErrors(response.validation_errors);
            }
            $(".validation-error, .request-password").hide();
            $(".alert-success").slideDown();
        });
    });
});
