$(function(){
    // decide whether to display a suggestion that they send an email
    // about a noteworthy update
    var email_flag = false;
    if ( $('span.interpreter').length && $("ins, del").length) {
        var noteworthy = ["date","time","type","interpreters","location"];
        $("ins, del").each(function(){
            var field = ($(this).parent().prev("div").text().trim());
            if (noteworthy.includes(field)) {
                email_flag = true;
                return false;
            }
        });
    }
    console.log(`email flag? ${email_flag}`);// to be continued

    $("#btn-email").on("click",function(e){e.preventDefault();});

    $("#email-dialog").on("show.bs.modal",function(event){
    });
    $("#btn-add-recipients").on("click",function(e){
        e.preventDefault();
        if (! $("#email-dropdown input:checked").length) {
            $("#email-dropdown .validation-error").text(
                "select at least one recipient"
            ).show();
            return false;
        } else {
            $("#email-dropdown .validation-error").hide();
        }
        console.log("looks good, now do shit...");
        return true;

    });
    //$(".dropdown-menu .custom-control-input").on("click",function(e){e.preventDefault();})

});
