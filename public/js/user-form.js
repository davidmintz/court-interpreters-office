/**
 * public/js/user-form.js
 */

var $, displayValidationErrors;

/**
 * @todo
 * add event listener to look via xhr for existing person (interpreter) when
 * email field is set
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
    if (id && hatElement.children(":selected").data("is_judges_staff")) {
        $("#judge-div").show();
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
    var hat_element = $("#hat");
    // $("#role").on("change",function(){
    //     if ($(this).val() === "submitter") {
    //         console.log(hat_element.children(":selected").data());
    //     }
    // });
    var person_id_element =  $("input[name='user[person][id]']");
    $("#btn-submit").on("click", function(event){
        event.preventDefault();
        // make sure we find the existing person entity, if any
        var person_id =  person_id_element.val();
        var hat = hat_element.children(":selected").text();
        var is_interpreter = hat_element.children(":selected").text()
            .match(/staff.+interpreter/i);
        if (person_id || ! is_interpreter) { // then don't bother searching
            console.log("maybe we have a person id, or a non-interpreter");
            var data = $("#user-form").serialize();
            return $.post(document.location.href,data, postcallback);
        }
        /*
        else...
        they are creating a user account for a staff court interpreter,
        but have not provided the id for an existing person (by loading the
        form with the person id as route parameter), so we'll try to find one
        */
        var email = $("#email").val();
        $.getJSON("/admin/users/find-person",{email,hat})
        .then(function(response){
            console.log("hello? response from find-person");
            if (response.result && response.result.length) {
                /*
                we need to have received in the query result a person-object
                with matching hat, and active == true
                 */
                var people = response.result;
                var person_id;
                for (let i = 0; i < people.length; i++) {
                    let p = people[i];
                    if (p.hat === hat && p.active) {
                        console.log(`found active person with hat ${p.hat}`)
                        person_id = p.id;
                        break;
                    }
                }
                if (person_id) {
                    person_id_element.val(person_id);
                    var data = $("#user-form").serialize();
                    return $.post(document.location.href,data)
                }
            } else { // else, there is an issue

                return postcallback(
                { validation_errors :
                    { user :
                        { person :
                            { existing_entity_required : { shit:
                                `To create a user account for a staff interpreter,
                                there must first be an active staff interpreter in existence. Please
                                <a href="${window.basePath}/admin/interpreters/add">add the interpreter</a>
                                to your database first, then return here to set up the user account.`
                                }
                            }
                        }
                    }
                });
            }
        })
        .then((response)=>{
            if (response) {
                console.log("hello! another then(response)");
                postcallback(response);
            } else {
                console.debug("hmmm. last then(), response is "+typeof response);
            }
        });
    });
    $("#btn-delete").on("click",function(e){
        e.preventDefault();
        console.warn("FUCK?");
        var id = $(`input[name="user[id]"]`).val();
        console.warn(`boink! delete ${id}`);
        $.post(`${window.basePath}/admin/users/delete/${id}`)
         .done(function(res){
             console.warn(res.message);
             if (res.status !== "success" && res.message) {
                 $("div.status-message p").text(res.message)
                 .parent().removeAttr("hidden");
                 return;
             }
             return postcallback(res);
         })
         .fail(fail);
    });
});

var postcallback = function(response) {
    console.log("postcallback running ");
    if (response.status === "success") {
        document.location = `${window.basePath}/admin/users`;
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
