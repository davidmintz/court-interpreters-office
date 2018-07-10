var displayValidationErrors, $;

$(function(){
    $('#btn-submit').on("click",function(){
        
        $.post('/user/request-password',{
            email:$('#email').val()
        }).then(function(response){
            if (response.validation_errors) {
                return displayValidationErrors(response.validation_errors);
            }
        });
    });
});
