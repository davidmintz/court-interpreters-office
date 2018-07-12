var displayValidationErrors, $;
$(function(){
    $("#btn-submit").on("click",function(event){
        event.preventDefault();
        var data = $('#form-reset-password').serialize();
        $.post(document.location.href,data).then(function(response){
            console.log(response);
        });
    });
});
