/**
 * public/js/user-profile.js. depends on form-utilities.js et al
 */

/*
global $, fail, displayValidationErrors
*/
$(function(){
    $("form button[type='submit']").on("click",function(e){
        e.preventDefault();
        $.post(document.location.href,$("form").serialize())
        .then(function(response){
            if (response.validation_errors ){
                errors = response.validation_errors;
                if (errors.user) {
                    if (errors.user.person) {
                        Object.assign(errors, errors.user.person);
                        delete errors.user.person;
                    }
                    Object.assign(errors, errors.user);
                    delete errors.user;
                }
                return displayValidationErrors(errors);
            }

            $(".validation-error").hide();
            $("#status-message").slideDown();
            $("form").hide();
        }).fail(fail);
    });
    
});