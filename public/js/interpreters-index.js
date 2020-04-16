/** public/js/interpreters-index.js */

/* global  $, displayValidationErrors, fail */

$(function(){
    $(`[data-toggle="tooltip"]`).tooltip();
    $(".modal-header button[data-hide]").on("click",()=>$("#modal-email").modal("hide"));   
    var languageSelect = $("#language_id");
    var languageButton = $("#btn-search-language");
    languageButton.on("click",function(event){
        event.preventDefault();
        var language_id = languageSelect.val() || "0";
        var url = languageButton.attr("href");
        url += "/language/" + language_id;
        url += "/active/"+$("#active").val();
        var security = $("#security_clearance_expiration").val();
        url += "/security/"+security;
        document.location = url;
    });    
    var nameElement = $("#name");
    nameElement.autocomplete({
        source : "/admin/interpreters",
        minLength : 2,
        select : function( event, ui ) {
            nameElement.data({ interpreterName : ui.item.label, interpreterId: ui.item.id });
            $("#btn-search-name").trigger("click");
        }
    });
    $("#btn-search-name").on("click",function(event){
        event.preventDefault();
        var name = nameElement.val().trim();
        if (! name) {
            return;
        }

        var url = `${window.basePath}/admin/interpreters`;
        var selected = nameElement.data();
        // if we have an interpreter id, use it in the url
        if (name === selected.interpreterName) {
            url  += "/" + selected.interpreterId;
        } else {
            //'route' => '/name/:lastname[/:firstname]',
            var pos = name.lastIndexOf(",");
            if (-1 === pos) {
                url += "/name/"+name.trim();
            } else {
                var lastname = encodeURIComponent(name.substring(0,pos).trim());
                var firstname = encodeURIComponent(name.substr(pos+1).trim());
                url += "/name/"+ lastname + "/" + firstname;
            }
        }
        document.location = url;
    });

    /**
     * require re-authentication to decrypt and display ssn and dob
     */

    $("#auth-submit").on("click",function(){
        var input = {
            identity : $("#form-login input.thing1").val(),
            password : $("#form-login input.thing2").val(),
            login_csrf : $("input[name=\"login_csrf\"").val()
        };
        var url = /*window.basePath +*/ "/login";
        $.post(url, input, function(response)
        {
            if (response.validation_errors) {
                //refresh the CSRF token
                $("input[name=\"login_csrf\"").val(response.login_csrf);
                // since we hacked the names, translate them back
                var errors = {};
                errors[$(".thing1").attr("id")] = response.validation_errors.identity;
                errors[$(".thing2").attr("id")] = response.validation_errors.password;
                return displayValidationErrors(errors);
            }
            if (response.authenticated) {
                $.post("/vault/decrypt",{
                    dob  : $("#encrypted_dob").val(),
                    ssn  : $("#encrypted_ssn").val(),
                    csrf : response.csrf
                },function(data){
                    /** @todo handle errors! */
                    $("#dob").text(data.dob);
                    $("#ssn").text(data.ssn);
                    $("#login-modal").modal("hide");
                });
            } else {
                return $("#div-auth-error").text(response.error).show();
            }
        });
    });

    /**
     * emails current listing to email address they enter into the dialog
     */
    $("#btn-send-list").on("click",function(){
        $("#modal-email button").attr("disabled",true);
        var form = $("#form-send-list");
        var data = form.data().params;
        data.email = $("input[name=email]").val().trim();
        data.recipient = $("input[name=recipient]").val().trim();
        data.csrf = $("[name=csrf]").val();
        $.post(form.attr("action"),data)
            .then((res)=>{
                $("#modal-email button").removeAttr("disabled");
                if (res.validation_errors) {
                    return displayValidationErrors(res.validation_errors);
                }
                console.log(res);
                var success_message = $("#email-success");
                if (res.status === "success") {
                    success_message.removeClass("d-none").addClass("d-flex align-items-start");
                    $("#modal-email").one("hide.bs.modal",()=>{
                        success_message.addClass("d-none").removeClass("d-flex align-items-start");
                        document.getElementById("form-send-list").reset();                        
                    });
                }
                form.one("change",()=> success_message.addClass("d-none").removeClass("d-flex align-items-start"));
            })
            .fail(fail);
    });
});
