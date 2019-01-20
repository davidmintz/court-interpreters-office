/**
 * public/js/user-form.js
 */

var $, displayValidationErrors;

/**
 * @todo
 * add event listener to look via xhr for existing person (interpreter) when email field is set
 */

$(document).ready(function(){
    // dynamically re-populate role element depending on hat element value
    var hatElement = $('#hat');
    var roleElement = $('#role');
    hatElement.on('change',function(){
        var hat_id = hatElement.val();
        if (! hat_id) {
            return;
        }
        $.getJSON('/admin/users/role-options/'+hat_id,{}, function(data){
            //console.log(data);
            var options = data.map(function(item){
                return $('<option>').val(item.value).text(item.label)
                    .data({type: item.type});
            });
            roleElement.children().slice(1).remove();
            roleElement.append(options);
            if (options.length === 1) {
                roleElement.children(":last").attr({selected:"selected"});
            }
        });
    });
    var id = $("input[name='user[id]']").val();
    if (id) {
        //hatElement.trigger("change");
    }
    // help enforce logical consistency between user-account "active"
    // and person "active" properties
    var userActiveElement = $('#user-active');
    var personActiveElement = $('#person-active');
    $('#user-active, #person-active').on("change",function(event){
        if (! personActiveElement.is(":checked")) {
            userActiveElement.prop("checked",false);
        }
        if (userActiveElement.is(":checked")) {
            personActiveElement.prop("checked",true);
        }
        if (event.target.id === 'user-active')  {
            if (! userActiveElement.is(':checked')  && ! personActiveElement.is(':checked')) {
                personActiveElement.prop("checked",true);
                userActiveElement.prop("checked",true);
            }
        }
    });
    $("#email").on("change",function(){

    });
    var person_id_element =  $("input[name='user[person][id]']");
    var hat_element = $("#hat");
    /** this is insane, a/k/a work-in-progress */
    $("#btn-submit").on("click", function(event){
        // make sure we find the existing person entity, if any
        event.preventDefault();
        var person_id =  person_id_element.val();
        var hat = hat_element.children(":selected").text();
        var is_interpreter = hat_element.children(":selected").text()
            .match(/staff.+interpreter/i);
        if (person_id || ! is_interpreter) { // then don't bother searching
            console.log("either we have a person id, or a non-interpreter");
            var data = $("#user-form").serialize();
            return $.post(document.location.href,data);
                //.then(postcallback);
        }
        /*
        else...
        they are creating a user account for a staff court interpreter,
        but have not provided the id for an existing person (by loading the
        form with the person id as route parameter)
        */
        var email = $("#email").val();
        $.getJSON("/admin/users/find-person",{email,hat})
        .then(function(response){
            console.warn("hello?");
            if (response.person_id) {
                var data = $("#user-form").serialize();
                return $.post(document.location.href,data)
            }
            if (response.result && response.result.length) {
                /*
                we need to have received in the query result a person-object
                with matching hat, and active == true
                 */
                var people = response.result;
                var person_id;
                for (let i = 0; i < people.length; i++) {
                    let p = people[i];
                    if (p.hat === hat) {
                        console.warn(`found person with hat ${p.hat}`)
                    }
                }// to be continued
                if (! person_id) {
                    console.warn("the fuck?");
                    return postcallback({
                        validation_errors :
                            { user :   { person :
                                {
                                    shit : "you need to deal with shit first"
                                }
                            }
                        }
                    });

                } else {
                    console.warn("huh?");
                }
            } else {
                var data = $("#user-form").serialize();
                console.log("posting...");
                return $.post(document.location.href,data)
            }
        })
        .then((response)=>{
            console.log("we got a response?");
            console.log(response||"no");
        });
    });
});

var postcallback = function(response) {
    //console.log(response);
    console.warn("postcallback running ");
    if (response.status === "success") {
        document.location = "/admin/users";
        return;
    }
    if (response.validation_errors) {
        console.log("hello! we have validation errors.");
        var errors = response.validation_errors;
        if (errors.user) {
            displayValidationErrors(errors.user);
            if (errors.user.person) {
                displayValidationErrors(errors.user.person);
                console.warn(errors.user.person);
            }
        }
        if (errors.csrf) {
            displayValidationErrors(errors);
        }
        return;
    }
};

gack = () =>
{
    $("#lastname").val("Somebody");
    $("#firstname").val("John");
    $("#hat").val("1");
    $("#role").val("2");
    $("#user-active").prop("checked","checked");
    $("#username").val("john");
    $("#email").val("john_somebody@some.uscourts.gov")

};
