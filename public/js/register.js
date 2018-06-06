$(function(){
    //var fieldsets = $("fieldset");
    $('#btn-next').on("click",function(event){
        event.preventDefault();
        var id = $("fieldset:visible").attr('id');
        if (id === "fieldset-personal-data" || id === "fieldset-hat") {
            var params = $("fieldset:visible").serialize();
            console.log("submitting "+id);
            $.post("/user/register/validate?step="+id,params).then(
                function(response){
                    if (response.validation_errors) {
                        if ( id === "fieldset-personal-data") {
                            var errors = response.validation_errors.person
                            return displayValidationErrors(errors);
                        } else {
                            console.log("shit is step two?");
                            console.log(response.validation_errors);
                        }
                    } else {
                        $(".carousel").carousel("next");
                    }
                }
            );
        } else {
            console.log("submit the whole form");
        }
    });
});
