/** public/js/defendant-form.js */

/* global  $, displayValidationErrors, fail */

const is_literal_duplicate = (entity) => {
    return entity.surnames === $("#surnames").val().trim() &&
    entity.given_names === $("#given_names").val().trim();
};
$(function(){
    var form = $("#defendant-form");

    $("#btn-submit").on("click",function(event){
        event.preventDefault();
        var data = form.serialize();
        var action = $("input[name=id]").val() ? "update":"insert";

        $.post(form.attr("action"),data)
        .then(
            function(response)
            {
                if (response.validation_errors) {
                    return displayValidationErrors(response.validation_errors);
                } else {
                    $(".validation-error").hide();
                }
                var url;
                if (response.status === "success") {
                    url = form.data().redirect_url || "/admin/defendants";
                    return  window.document.location = `${window.basePath||""}${url}`;
                }
                if (response.existing_entity) {
                    var existing = response.existing_entity;
                    var name = `${existing.surnames}, ${existing.given_names}`;
                }
                if (response.duplicate_entry_error) {
                    var msg;
                    $("#error-div h3").text("error: duplicate entry");
                    if (action === "insert") {
                        if (is_literal_duplicate(existing)) {
                            msg = "This name is already in your database.";
                        } else {
                        // inexact duplicate. they should update, not insert
                            url = `${window.basePath||""}/admin/defendants/edit/${existing.id}`;
                            msg = `This name cannot be inserted because there is
                        already an inexact duplicate of it in your database:
                        <strong>${name}</strong>.
                        You can <a href="${url}">update it</a> instead.`;
                        }
                        return $("#error-message").html(msg).parent().show();
                    } else { /* we are the update form */
                        console.warn(
                            "update returned duplicated entry error, deal with it");
                    }
                } else if (response.inexact_duplicate_found) {
                    form.prepend($("<input>").attr({type:"hidden",
                        name:"duplicate_resolution_required",value:1}));
                    $("#deft-existing-duplicate-name").text(name);
                    var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
                    console.warn("daFUQ?");
                    return $(shit).show();
                } else {
                    console.warn("all good?");
                    url = form.data().redirect_url || "/admin/defendants";
                    window.document.location = `${window.basePath||""}${url}`;
                }
            })
            .fail((response)=> {
                $("#error-div h3").text("system error");
                fail(response);
            });
    });

    form.on("click","#btn-select-all, #btn-invert-selection",function(event){
        event.preventDefault();
        var checkboxes = $("form input[type=checkbox]");
        if ($(event.target).attr("id")=="btn-select-all") {
            checkboxes.prop("checked",true);
        } else {
            checkboxes.each(function(){
                var checkbox = $(this);
                var checked = checkbox.prop("checked");
                checkbox.prop("checked",!checked);
            });
        }
    });
    $("#btn-delete").on("click",function(event){
        event.preventDefault();
        if (! window.confirm(
            "Are you sure you want to delete this defendant name?")) {
            return;
        }
        var name = form.data("defendant_name");
        var url = form.data("redirect_url")
            ||`${window.basePath || ""}/admin/defendants`;
        var id = $(`input[name="id"]`).val();
        $.post("/admin/defendants/delete/"+id,{ name })
        .then(() => window.document.location = url)
        .fail(fail);
    });
});
