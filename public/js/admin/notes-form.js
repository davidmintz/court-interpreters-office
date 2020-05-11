/*
 * public/js/admin/notes/notes-form.js
 */

/*
global $, fail, displayValidationErrors, moment
*/
/*
onSelect
Type: Function( String dateText, Object inst )
Default: null
Called when the datepicker is selected. The function receives the selected date
as text and the datepicker instance as parameters.'this' refers to the associated
input field.
*/
var append_motd_date = function(dateText) {
    var dateObj = moment(dateText,"YYYY-MM-DD");
    var dateString = dateObj.format("ddd DD-MMM-YYYY");
    if ($(`#dates input[value="${dateText}"]`).length) {
        // already exists
        return;
    }
    var shit = `<div class="d-inline border border-info rounded mr-1 pl-2 text-monospace motd-date" style="font-size:90%">
    <input type="hidden" name="dates[]" value="${dateText}">
    ${dateString} <button class="btn btn-outline-secondary btn-sm btn-remove-item my-1 border-0" title="remove">
    <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove this defendant</span></button></div>`;
    $("#dates").append(shit);
};

var delete_note = function(){
    var form = $("#notes-form");
    var id = $("input[name=\"id\"]").val();
    var type = $("input[name='type']").val();
    var token = $("input[name=csrf]").val();
    $.ajax({
        method : "DELETE",
        url : `${window.basePath}/admin/notes/delete/${type}/${id}`,
        headers : {"X-Security-Token":token}
    }).then(
        (res)=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            }
            var html = `<div class="alert alert-success">This ${type.toUpperCase()} has been deleted.</div>`;
            form.replaceWith(html);
        }
    ).fail(fail);
};
var dp_defaults = {
    dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true
};

$(function(){
    console.warn("Here's Johnny");
    $("#calendar-motd").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motd").data("date")})
    );
    $("#calendar-motw").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motw").data("date")})
    );

});
