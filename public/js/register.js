$(function(){
    //var fieldsets = $("fieldset");
    $('#btn-next').on("click",function(event){
        event.preventDefault();
        var params = $("fieldset:visible").serialize();
        $.get("/user/register/validate",params).then(
            function(response){
                if (response.validation_errors) {
                    return displayValidationErrors(response.validation_errors);
                } else {
                    $(".carousel").carousel("next");
                }
            }
        );


    });
});
