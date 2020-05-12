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

const display_note = function(response,dateText,type){
    var date = moment(dateText,"YYYY-MM-DD");
    var content_div = $(`#${type}-content`);
    var header = content_div.siblings("h4");
    if (type === "motw") {
        var dow = parseInt(date.format("E"));
        if (dow !== 1) {
            date.subtract(dow - 1, "days");
        }
    }
    var key = type.toUpperCase();
    header.text(`${key} for ${date.format("dddd DD-MMM-YYYY")}`);
    if (response[key]) {
        var note = response[key];        
        content_div.html(note.content);                        
        content_div.append(get_note_edit_button(note,type,dateText));
    } else {                                
        content_div.html([`<p class="font-italic mx-2">no ${key} for ${date.format("ddd DD-MMM-YYYY")}</p>`,
            get_note_edit_button({},type,dateText)]);                
    } 
};

var dp_defaults = {
    dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true,
    /**
     * loads the MOTD/MOTW, if any, for the date selected
     */
    onSelect : function(dateText, instance){
        var type = instance.id.substring(9);
        // are we in batch-edit mode?
        var multidate_mode = $("#motd-content form").data("multiDate");
        if (multidate_mode) {                   
            return append_motd_date(dateText);
        }        
        // url to fetch note
        var url = `/admin/notes/date/${dateText}/${type}`;         
        $.getJSON(url).then((res)=>{display_note(res,dateText,type);});    
    }
};


$(function(){

    // intialize the two datepickers 
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
        var form = $(this).closest("form");
        var div = $("div.multi-date");
        form.data({multiDate: !form.data("multiDate")});
        // state to which we have just changed
        var enabled = form.data("multiDate");
        console.warn(`shit is now: ${enabled ? "enabled":"disabled"}`)
        if (enabled) {
            div.removeAttr("hidden");
            console.log("display/enable multi-date shit");
            $("#motd-content textarea[name=content]").attr({disabled:true}).hide();

        } else {
            console.log("disable/hide multi-date shit");
            $("#motd-content textarea").attr({disabled:false}).show();
            div.attr({hidden:true});
        }
        // remove date thingies
    }).on("click",".btn-remove-item",function(e){
        e.preventDefault();
        $(this).closest("div").remove();
    });
    // load the editing form
    $(".note-content").on("click","#btn-editor-motd, .note-content #btn-editor-motw",
        function(e){
            e.preventDefault();
            var path = this.href.split("/").slice(3).join("/");            
            var div = $(this).parent();
            $.get(`/${path}`).then((html)=>div.html(html));
        }
    // submit the editing form
    ).on("click","button.btn-success",function(e){
        e.preventDefault();
        console.log("time to rock and roll: save");
        var form = $(this).closest("form");
        var type = $("input[name='type']").val();        
        var is_multidate = type === "motd" && form.data("multiDate");
        if (is_multidate) {
            $("#dates input[type=\"hidden\"]").removeAttr("disabled");
        } else {
            $("#dates input[type=\"hidden\"]").attr({disabled:true});
        }
        var id = $("input[name=\"id\"]").val();
        var url, method;
        var data = form.serialize();
        if (id) {
            // update
            console.log("doing an update?");
            url = `/admin/notes/update/${type}/${id}`;
            method = "PUT";
        } else {
            // create
            console.log("doing a create?");
            url = `/admin/notes/create/${type}`;
            if (type === "motw") {
                // add week_of parameter
                var dp = $(`#calendar-${type}`);
                data += "&week_of="+moment(dp.datepicker("getDate")).format("YYYY-MM-DD");
            }            
            method = "POST";
        } 
        console.log(`gonna ${method} to ${url}...`);
        $.ajax({url, method, data 
        }).then((res)=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            }
            if (res.status === "success") {
                if (! is_multidate) {
                    // the datepicker could be inconsistent with the form
                    var dp = $(`#calendar-${type}`);
                    var dp_date = moment(dp.datepicker("getDate")).format("YYYY-MM-DD");
                    var form_date = $("input[name=date]").val();
                    if (dp_date !== form_date) {
                        dp.datepicker("setDate",form_date);
                    }
                    var url = `/admin/notes/date/${dp_date}/${type}`;
                    $.getJSON(url).then(res=>{
                        var content = res[type.toUpperCase()].content;
                        form.before(`<div class="alert alert-success rounded border-success shadow-sm px-1"><span class="fa fa-check text-success ml-2"></span> ${type.toUpperCase()} saved.</div>`);
                        form.after(get_note_edit_button({id:id},type,dp_date));
                        form.replaceWith(content);
                    });
                    
                } else { // this is multi-date edit mode, toggle back
                    $("#btn-multi-date").trigger("click");
                    var count = $(".motd-date").length;
                    form.before(
                        `<div class="alert alert-success rounded border-success shadow-sm px-1"><span class="fa fa-check text-success ml-2"></span> Updated ${count} MOTDs.</div>`);
                    $.getJSON(url).then(res=>{
                        var content = res[type.toUpperCase()].content;
                        //form.before(`<div class="alert alert-success rounded border-success shadow-sm px-1"><span class="fa fa-check text-success ml-2"></span> ${type.toUpperCase()} saved.</div>`);
                        form.after(get_note_edit_button({id:id},type,dp_date));
                        form.replaceWith(content);
                    });
                }
                var success_message = form.prev("div.status");
                success_message.children(".message").text(`${type} has been saved.`);
                success_message.removeClass("d-none").addClass("d-flex align-items-start");
                //$("form .alert-success").prepend(`<p>Successfully created/updated MOTDs for ${count} dates.</p>`).removeAttr("hidden");
            }

            if (res.status === "error") {
                var error_div = $("<div>").addClass("alert alert-warning validation-error");
                // a modification timestamp mismatch?
                var ours = $("input[name=\"modified\"]").val();
                var theirs = res.modified || null;
                if (theirs && theirs !== ours) {
                    error_div.text(`${res.message} Please try again.`);
                    $("input[name=\"modified\"]").val(theirs);
                    $("textarea#content").text(res[type].content);
                } else {
                    error_div.text(res.message);
                }
                form.prepend(error_div);
            }
        }).fail((res)=>{
            console.log(res);
        });
    }).on("click","#btn-cancel-edit",function(e){
        e.preventDefault();
        console.log("cancel edit");        
        // var type = $("input[name='type']").val();
        var multidate = $(this).closest("form").data("multiDate");
        if (multidate) { // toggle it off
            $("#btn-multi-date").trigger("click");
        } else {
            // reload
            var form = $(this).closest("form");
            var type = form.data("type");
            var date = $(this).siblings("input[name='date']").val();
            console.warn("date is FUCKING WHAT? "+date);
            var url = `/admin/notes/date/${date}/${type}`;
            console.warn("fetching: "+url);
            $.getJSON(url).then( response=>display_note(response,date,type) );

        }
    });

});
