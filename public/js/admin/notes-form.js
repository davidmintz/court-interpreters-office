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
const append_motd_date = function(dateText) {
    var dateObj = moment(dateText,"YYYY-MM-DD");
    var dateString = dateObj.format("ddd DD-MMM-YYYY");
    if ($(`#dates input[value="${dateText}"]`).length) {
        // already exists
        return;
    }
    var shit = `<div class="d-inline border border-info rounded mr-1 pl-2 text-monospace motd-date" style="font-size:90%">
    <input type="hidden" name="dates[]" value="${dateText}">
    ${dateString} <button class="btn btn-outline-secondary btn-sm btn-remove-item my-1 border-0" title="remove">
    <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></div>`;
    $("#dates").append(shit);
};

const delete_note = function(){
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

const get_note_edit_button = function(note, type, dateText){
    var path,action;
    if (note.id) { 
        action ="edit";
        path = `edit/${type}/${note.id}/date/${dateText}`;
    } else {
        action = "create";
        path = `create/${type}/${dateText}`;
    }
    return `<a class="btn btn-primary fas fa-edit" id="btn-editor-motd" role="button" href="/admin/notes/${path}"><span class="sr-only">${action}</span></a>`;
};
var dp_defaults = {
    dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true,
    onSelect : function(dateText, instance){
        var type = instance.id.substring(9);
        // are we in batch-edit mode?
        var multidate_mode = $("#notes-form").data("multiDate");
        if (multidate_mode) {                   
            return append_motd_date(dateText);
        }
        var key = type.toUpperCase();
        // url to fetch note
        var url = `/admin/notes/date/${dateText}/${type}`;
        // div to put it in
        var content_div = $(`#${type}-content`);
        var header = content_div.prev("h4");
        $.getJSON(url).then((res)=>{
            if (res[key]) {
                var note = res[key];
                header.text(`${key} for ${note.date || note.week_of}`);
                content_div.html(note.content);                        
                content_div.append(get_note_edit_button(note,type,dateText));
            } else {
                var date = moment(dateText,"YYYY-MM-DD");
                if (key === "MOTW") {
                    var dow = parseInt(date.format("E"));
                    if (dow !== 1) {
                        date.subtract(dow - 1, "days");
                    }
                }
                header.text(`${key} for ${date.format("dddd DD-MMM-YYYY")}`);
                content_div.html([`<p>no ${key} for ${date.format("ddd DD-MMM-YYYY")}</p>`,]);
                content_div.append(get_note_edit_button({},type,dateText));
            }
            console.log("fuck you?");
        });    
    }
};

$(function(){
    console.warn("Here's Johnny");
    $("#calendar-motd").datepicker(
        Object.assign(dp_defaults,{
            defaultDate : $("#calendar-motd").data("date"),              
        })
    );
    $("#calendar-motw").datepicker(
        Object.assign(dp_defaults,{
            defaultDate : $("#calendar-motw").data("date"),              
        })
    );
    
    // toggle MOTD batch-editing
    $("#motd-content").on("click","#btn-multi-date", function(e){
        e.preventDefault();
        var form = $("#notes-form");
        var div = $("div.multi-date");
        form.data({multiDate: !form.data("multiDate")});
        // state to which we have just changed
        var enabled = form.data("multiDate");
        if (enabled) {
            div.removeAttr("hidden");
            console.log("display/enable multi-date shit");
            $("#notes-form textarea[name=content]").attr({disabled:true}).hide();

        } else {
            console.log("disable/hide multi-date shit");
            $("#notes-form textarea").attr({disabled:false}).show();
            div.attr({hidden:true});
        }
        // remove date thingies
    }).on("click",".btn-remove-item",function(e){
        e.preventDefault();
        $(this).closest("div").remove();
    });

    $(".note-content").on("click","#btn-editor-motd, .note-content #btn-editor-motw",
        function(e){
            e.preventDefault();
            console.log("loading the form via "+this.href);
            $(this).parent().load(this.href);
        });
});
