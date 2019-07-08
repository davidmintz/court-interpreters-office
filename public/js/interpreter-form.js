/**
 * attach event listeners
 *
 * @todo refactor to make less long and monolithic
 */
//*/
$(function(){

    $('input.date').each(function(i,element){
        if (element.value.match(/^\d{4}-\d\d-\d\d$/)) {
            element.value = element.value.replace(/(\d{4})-(\d\d)-(\d\d)/,"$2/$3/$1");
        }
    });
   // }
    // in order for server-side partial validation to know the context
    var action = $('#interpreter-form').attr('action').indexOf('/edit/') > -1 ?
            'update' : 'create';

    // pad the div holding the checkbox
    $("#person-active").parent().addClass("pt-2");

    // make the first tab active, unless we are coming back from
    // a validation failure

    if (! $(".validation-error").text()) {
       $('#nav-tabs li:first a').tab("show");
    } else {
       // not entirely satisfactory, it makes shit jump,
       // but better than nothing for now
       var pane = $(".validation-error").not(":empty").first().closest('div.tab-pane');
       var id = pane.attr("id");
       // console.warn(id +  " is our id, bitch");
       $('#nav-tabs a[aria-controls="'+id+'"]').tab("show");
       $(".validation-error").each(function(){
            var div = $(this);
            if (-1 != div.text().indexOf("language is required")) {
                div.addClass("language-required");
            }
       });
    }
    //if (! Modernizr.inputtypes.date) {
        $('input.date').datepicker({
            changeMonth: true,
            changeYear: true,
            constrainInput : false,
            selectOtherMonths : true,
            showOtherMonths : true
        });
        // if the dob field is enabled, set datepicker options
        if (!($('#dob').val())) { // i.e., if it isn't just '**********'
            $('#dob').datepicker("option",{
                maxDate: "-18y",
                minDate : "-100y",
                yearRange : "-100:-18"
            });
        }
        /** @todo
         * set datepicker options to display year for dob, if element exists
         * set options to constrain security_clearance, fingerprint etc date ranges
         * NOTE TO SELF: setting the relative maxDate to 0 has the interesting side
         * effect of making invalid dates NOT display in cases like 04/17/23472348789374
         */
        /*
        $('#fingerprint_date, #oath_date, #security_clearance_date').datepicker("option",{
            maxDate: 0
        });        */
    //}
    /**
     * add a working language.
     * @todo solve case where "at least one language is required" is printed twice
     */
    var languageSelect = $('#language-select');
    $('#btn-add-language').on('click',function(event){
        event.preventDefault();
        var index;
        var language_id = languageSelect.val();
        if (! language_id) { return; }
        if ($('#language-'+language_id).length) { return; }
        // get the (server-side) index of the last fieldset
        var last = $('#languages .interpreter-language > select').last();
        if (last.length) {
            var m = last.attr("name").match(/\[(\d+)\]/);
            if (m.length) {
                index = parseInt(m.pop()) + 1;
            } else { /* this is an error. to do: do something? */ }
        } else {
            index = 0;
        }
        url = "/admin/interpreters/language-fieldset";
        $.get(url,{index : index, id : language_id },function(data){
            $('#languages-div').append(data);
            languageSelect.val("");
            $('#languages .language-required').remove();
        });
    });
     /* remove a language *********************************************/
    $('#languages').on("click", ".btn-remove-language",function(event){
        event.preventDefault();
        var div =  $(this).closest("div");
        var divs = div.add(div.prev());
        divs.slideUp(function(){divs.remove();});
    });

    $("form").on("change",".language-credential select",function(e){
        var value = $(this).val();
        if (value) {
            $(this).siblings(".validation-error:contains('required')").slideUp();
        }
    })

    // try to prevent the damn browser from autocompleting
    // http://stackoverflow.com/questions/31439047/prevent-browser-from-remembering-credentials-password/43874591#43874591
    $('#login-modal').on("show.bs.modal",function(){
         $('.thing1, .thing2').val("");  // doesn't work, either.
         // nor does this. shit.
         // maybe some carousel thing where you show them one form control
         // followed by the other
         $(".thing2").css({color:"white"}).on("focus",function(){$(this).css({color:"black"})});
         $(".thing1").on("focus",function(){
            console.log("am i imagining this?");
               $('.thing2').val("");
               console.warn("WTF?!");
        }).on("blur",function(){ $('.thing2').val("")});
    });
    $("#hat").on("change",function(){
        var element = $(this);
        if (element.val()) { // good enough for government work
            element.next(".validation-error").slideUp();
        }
    });

    /** validate each tab pane before moving on **/
    // note to self: isn't there a Bootstrap event to observe instead?
    $('a[data-toggle="tab"]').on('click', function (event,params) {
        //alert("shit?");
        var id = '#'+$('div.active').attr('id');
        if (id.indexOf('languages') !== -1 &&
           ! $(".interpreter-language input").length
        ) {
            if (! $('#languages-div .language-required').length) {
                $('#languages-div').append(
                    $('<div >').addClass("alert alert-warning validation-error language-required")
                    .text("at least one language is required")
                );
            }
            return false;
        } else {
            // this should now be redundant, right?
            $('#languages .language-required').remove();
        }
        var selector = id + " input, " + id + " select";
        var data =($(selector).serialize());
        //console.log(data);
        var that = this;
        /**
         * when they change panels, post the data in the panel they're leaving
         * and expect a possible JSON data structure containing validation error
         * messages
         */
        $.post('/admin/interpreters/validate-partial?action='+action,
            data,
            function(response){
                if (response.validation_errors) {
                    if (response.validation_errors.interpreterLanguages) {
                        $.each(response.validation_errors.interpreterLanguages,
                            function(i,error){
                                $('div.language-credential select').not(":disabled")
                                .children('option:selected[value=""]')
                                .closest("div.language-credential")
                                .children(".validation-error")
                                .text(error).show();
                                return false;
                            });
                    } else {
                        console.warn("shit has nada to do with languages?");
                        displayValidationErrors(response.validation_errors);
                    }

                } else {
                    /** @todo here's what sucks. use xhr instead for form submission rather than .submit() */
                    if (params && params.submit) {
                        // they hit the submit button
                        return $('#interpreter-form').submit();
                    }
                    $(id + " .validation-error").hide();
                    $(that).tab("show");
                }
            },'json'
        );
        return false;
    });
    // we make them re-authenticate to display the password and dob field values
    $('#auth-submit').on("click",function(){
        // a sorry-ass attempt to defeat autocompletion.
        // NOTE TO SELF:
        // if you give up on the form and revert the re-naming,
        // the handling of error-message presentation will also
        // have to be reverted
        var id = $('#form-login input.thing1').val();
        var password = $('#form-login input.thing2').val();
        var input = {
            identity : id,
            password : password,
            login_csrf : $('input[name="login_csrf"').val()
        };
        var url = /*window.basePath +*/ '/login';
        $.post(url, input, function(response){
            if (response.validation_errors) {
                //refresh the CSRF token
                $('input[name="login_csrf"').val(response.login_csrf);
                // since we hacked the names, translate them back
                var errors = {};
                errors[$('.thing1').attr("id")] = response.validation_errors.identity;
                errors[$('.thing2').attr("id")] = response.validation_errors.password;
                return displayValidationErrors(errors);
            }
            if (response.authenticated) {

                var encrypted_dob =  $('input[name="encrypted.dob"]');
                var encrypted_ssn =  $('input[name="encrypted.ssn"]');
                $.post('/vault/decrypt',{
                    dob  : encrypted_dob.val(),
                    ssn  : encrypted_ssn.val(),
                    csrf : response.csrf
                },function(response){

                    if (response.error) {
                        $('#div-auth-error').text("Error: "+response.error).removeClass("hidden");
                        return;
                    }
                    $('#ssn, #dob').removeAttr("disabled");
                    if (response.ssn) {
                        encrypted_ssn.remove();
                        $('#ssn').val(response.ssn);
                    }
                    if (response.dob) {
                        encrypted_dob.remove();
                        $('#dob')
                            .val(window.moment(response.dob,"YYYY-MM-DD").format("MM/DD/YYYY"))
                            .datepicker("option",{
                                maxDate: "-18y",
                                minDate : "-100y",
                                yearRange : "-100:-18",
                        });
                    }
                    // hide the modal dialog
                    $('#login-modal').modal('hide');
                    // hide the lock thingies
                    $('button[data-target="#login-modal"]').hide();
                    $('#ssn, #dob').css({width:'100%'});
                },'json');
            } else {
                return $('#div-auth-error').text(response.error).removeClass("hidden");
            }
        });
    });
    /**
    @todo run partial validation here?
    @todo to do is to fix this suckage causing unpleasant jumping from the default first tab
    to the first one with a validation error
    */
    $('#interpreter-form').on("submit",function(event){
        //event.preventDefault();
        if ($(".validation-error:visible").length) {
            console.warn("boink! not valid");
            //return false;
        }
        $('a[data-toggle="tab"]').trigger("click",{submit:true});
        return true;
    });

    $("#btn-delete").on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this interpreter?"))
        {
            return false;
        }
        var id = $("input[name='interpreter[id]']").val();
        var url = `/admin/interpreters/delete/${id}`;
        var name = `${$("#firstname").val()} ${$("#lastname").val()}`;
        $.post(url,{name})
        .done(()=>
            window.document.location = `${window.basePath||""}/admin/interpreters`)
        .fail(fail);
    });
});
test = function(){
    $('#languages-pane').tab("show");
    $('#language-select').val(62);
    $('#btn-add-language').trigger("click");
};
