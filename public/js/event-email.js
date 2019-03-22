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
    console.log(`email flag? ${email_flag}`);

    $("#btn-email").on("click",function(e){e.preventDefault();});

    $("#email-dialog").on("show.bs.modal",function(event){});

    //$(".dropdown-menu .custom-control-input").on("click",function(e){e.preventDefault();})

});
