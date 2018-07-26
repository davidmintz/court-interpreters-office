/**
 * public/js/user-form.js
 */

var $, displayValidationErrors;

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
    $("#btn-submit").on("click",function(event){
        event.preventDefault();
        var data = $("#user-form").serialize();
        $.post(document.location.href,data)
        .then(function(response){
            if (response.status === "success") {
                document.location = document.referrer || (window.basePath + '/admin/users');
                return;
            }
            if (response.validation_errors) {
                var errors = response.validation_errors;
                if (errors.user) {
                    displayValidationErrors(errors.user);
                    if (errors.user.person) {
                        displayValidationErrors(errors.user.person);
                    }
                }
                if (errors.csrf) {
                    displayValidationErrors(errors);
                }
                return;
            }
        });
    });
});
