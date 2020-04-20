
/* global displayValidationErrors, $, fail, appendJudge */
$(function(){
    /** fix the minumum height for (sliding) fieldsets */
    var h = $("#fieldset-personal-data").height();
    $("fieldset").css("min-height",h+"px");
    $("#carousel").on("slid.bs.carousel",function(){
        /** id of the current fieldset/slide */
        var i = $("fieldset:visible").index();
        if (i > 0) {
            $("#btn-back").show();
        } else {
            $("#btn-back").hide();
        }
    });
    $("[data-toggle=\"popover\"]").popover();
    
    $("#btn-back").on("click",function(event){
        event.preventDefault();
        $(".carousel").carousel("prev");
    });
    /** validate each section */
    $("#btn-next").on("click",function(event)
    {
        event.preventDefault();
        var params, id = $("fieldset:visible").attr("id");
        console.warn(id);
        if (id === "fieldset-hat") { // last step
            if (hasIncompleteJudgeSelection(id)) {
                $("#modal-add-judge .modal-body").html(
                    "Did you mean to select Judge <strong>" +
                    $("#judge-select option:selected").text()
                    + "</strong>?"
                );
                $("#modal-add-judge").modal();
                $("#btn-yes-add-judge").one("click",function(){
                    appendJudge(event);
                    $("#modal-add-judge").modal("hide");
                    $("#btn-next").trigger("click");
                });

            } else { // last step: submit the whole form

                params = $("#registration-form").serialize();
                $.post("/user/register",params)
                    .then(function(response) {
                        if (response.validation_errors) {
                            displayValidationErrors(response.validation_errors);
                            // if they managed to beat the inter-fieldset validation,
                            // put them back on the first fieldset with errors
                            var i = $("fieldset .validation-error").first()
                                .closest("fieldset").index();
                            if (i !== -1) {
                                $(".carousel").carousel(i);
                            }
                            return;
                        }
                        if (response.status == "success") {
                            var email = response.data.person.email;
                            var html = "We have sent an email to the address you "
                        +" provided (<strong>"+email+"</strong>) with "
                        +" instructions for verifying your email address. "
                        +" Please check your inbox.";
                            $("#registration-form").remove();
                            $("#success-message").html(html).show();
                        }
                    }).fail(fail);
            }
        }
        // inter-fieldset validation
        if (id === "fieldset-personal-data" || id === "fieldset-password") {
            params = $("fieldset:visible, #csrf").serialize();
            $.post("/user/register/validate?step="+id,params)
                .then(function(response) {
                    if (response.validation_errors) {
                        var errors = response.validation_errors;
                        var url = window.basePath + "/user/request-password";
                        // special case: duplicate account
                        if (errors.email && errors.email.callbackValue 
                                && errors.email.callbackValue.match(/user.*account.*email/i)) {
                            $("#modal-duplicate-account .modal-body").html(
                                `A user account has previously been created for this email address. 
                                If you need to reset your password, please go to <a href="${url}">${url}</a>.`);
                            $("#modal-duplicate-account").modal();
                        }
                        displayValidationErrors(errors);
                    } else {
                        $("fieldset:visible .validation-error").hide();
                        $(".carousel").carousel("next");
                    }
                });
        }
    });
    var hasIncompleteJudgeSelection = function(id)
    {
        return id === "fieldset-hat" && $("#judge-select").val();
    };

    //stuffIt();
});
///*
var stuffIt = function()
{
    $("#firstname").val("Wanker");
    $("#lastname").val("Gack");
    $("#email").val("wanker_gack@nysd.uscourts.gov");
    $("#hat").val(6).trigger("change");
    $("#judge-select").val(
        $("#judge-select option:contains(Cote)").attr("value")
    );
    $("#btn-add-judge").trigger("click");
    $("#password").val("dinkleDorf^B23");
    $("#password-confirm").val("dinkleDorf^B23");
    //$(".carousel").carousel(2);

};
