/** public/js/admin/availability-list.js */
/* global $, fail, displayValidationErrors */
var additions = new Set();
var removals = new Set();
$(function(){
    $(`li input[type="checkbox"`).on("click",function(){
        var id = $(this).val();
        if ($(this).prop("checked")) {
            additions.add(id);
            if (removals.has(id)) {
                removals.delete(id);
            }
        } else {
            removals.add(id);
            if (additions.has(id)) {
                additions.delete(id);
            }
        }
    });
    $("#btn-save").on("click",function(){
        if (0 === additions.size + removals.size) {
           return;
        }
        // payload
        var data = { csrf: $(`[name="csrf"]`).val()};
        if (removals.size) {
            data.remove = Array.from(removals);
        }
        if (additions.size) {
            data.add = Array.from(additions);
        }
        var url = "/admin/interpreters/availability/list/update";
        $.post(url,data)
        .then((res) => { 
            if (res.validation_errors) {
                console.log("shit failed validation");
                return displayValidationErrors(res.validation_errors);
            }
            $("#message-success p").text(                    
                `The availability-solicitation list has been updated successfully (${res.added} added, ${res.removed} removed).`
            ).parent().show();
            $(`li input[type="checkbox"`).one("click",()=>$("#message-success").slideUp());
            
        })
        .fail(fail);
    });
});