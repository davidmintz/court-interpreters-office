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
    dateObj = moment(dateText,"YYYY-MM-DD");
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
    var id = $(`input[name="id"]`).val();
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
    );
}

$(function(){
    var dp_defaults = {
        dateFormat:"yy-mm-dd",
        showOtherMonths : true,
        selectOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        onSelect :  function(dateText,instance) {
            // type of note: either 'motd' or 'motw'
            var type = instance.id.substring(9);
            // are we in batch-edit mode?
            var multidate_mode = $("#notes-form").data("multiDate");

            if (multidate_mode && type === "motd") {
                return append_motd_date(dateText);
            }
            // url to get note
            var url = `/admin/notes/date/${dateText}/${type}`;
            // div to put it in
            var content_div = $(`#${type}-content`);
            // JSON response looks like { MOTD: { ... }}
            var key = type.toUpperCase();
            // button for loading form
            var form_button = $(`#btn-editor-${type}`);
            // url for loading form
            var form_url;
            $.getJSON(url)
            .then(function(res){
                if (res[key]) {
                    content_div
                        .html(res[key].content)
                        .prev("h5").text(res[key].date || `week of ${res[key].week_of}`);
                    // e.g., https://office.localhost/admin/notes/edit/motd/4581/date/2019-10-23
                    form_url = `${window.basePath}/admin/notes/edit/${type}/${res[key].id}/date/${dateText}`;
                } else {  // fuck, so much effort!
                    var h5, date = moment(dateText,'YYYY-MM-DD');
                    if (key === "MOTW") {
                        // figure out most recent Monday
                        var dow = parseInt(date.format("E"));
                        if (dow !== 1) {
                            date.subtract(dow - 1, "days");
                        }
                        h5 = `week of ${date.format("dddd DD-MMMM-YYYY")}`;
                    } else {
                        h5 = date.format("dddd DD-MMMM-YYYY");
                    }
                    content_div
                        .html(`<p class="font-italic no-note">no ${key} for this date</p>`)
                        .prev("h5").text(h5);
                    form_url = `${window.basePath}/admin/notes/create/${type}/${dateText}`;
                }
                var verbiage = form_url.indexOf("edit") > -1 ? "edit this":"create a";
                form_button.attr({href:form_url, title:`${verbiage} ${type}`});
                if (! form_button.is(":visible")) {
                    form_button.show();
                }
            });
        }
    };
    $("#calendar-motd").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motd").data("date")})
    );
    $("#calendar-motw").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motw").data("date")})
    );
    /** handler for loading edit|create form */
    $("#tab-content-notes").on("click","#btn-editor-motd, #btn-editor-motw",function(e){
        e.preventDefault();
        var btn = $(this);
        var path = this.href.split("/").slice(3).join("/");
        var content_div = btn.prev("div");
        $.get(`/${path}`).then(function(html){
            btn.hide();
            content_div.html(html);
        });
    });
    /** form submission handler for edit|create form */
    $("#tab-content-notes").on("click","#notes-form button.btn-success",function(e){
        e.preventDefault();
        var form = $("#notes-form");
        var type = $("input[name='type']").val();
        var is_multidate = type === 'motd' && form.data("multiDate");
        if (is_multidate) {
            $(`#dates input[type="hidden"]`).removeAttr("disabled");
        } else {
            $(`#dates input[type="hidden"]`).attr({disabled:true});
        }
        var id = $(`input[name="id"]`).val();
        var url, method;
        if (id) {
            // update
            console.log("doing an update?");
            url = `/admin/notes/update/${type}/${id}`;
            method = 'PUT';
        } else {
            // create
            console.log("doing a create?");
            url = `/admin/notes/create/${type}`;
            method = 'POST';
        }
        $.ajax({url, method, data : form.serialize()
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
                    $(`#calendar-${type} a.ui-state-active`).trigger("click");
                } else { // this is multi-date edit mode, toggle back
                    $("#btn-multi-date").trigger("click");
                    var count = $(".motd-date").length;
                    $("form .alert-success").prepend(`<p>Successfully created/updated MOTDs for ${count} dates.</p>`).removeAttr("hidden");
                    //console.log(res);
                }
            }

            if (res.status === "error") {
                var error_div = $(`<div>`).addClass("alert alert-warning validation-error");
                // a modification timestamp mismatch?
                var ours = $(`input[name="modified"]`).val();
                var theirs = res.modified || null;
                if (theirs && theirs !== ours) {
                    error_div.text(`${res.message} Please try again.`);
                    $(`input[name="modified"]`).val(theirs);
                    $("textarea#content").text(res[type].content);
                } else {
                    error_div.text(res.message);
                }
                form.prepend(error_div);
            }
        }).fail((res)=>{
            console.log(res);
        });
    }) // cancel edit
    .on("click","#btn-cancel-edit",function(e){
        e.preventDefault();
        var type = $("input[name='type']").val();
        var multidate = $("#notes-form").data("multiDate");
        if (multidate) { // toggle it off
            $("#btn-multi-date").trigger("click");
        } else {
            $(`#calendar-${type} a.ui-state-active`).trigger("click");
        }
    }) // toggle batch-editing mode
    .on("click","#btn-multi-date",function(e){
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
    }) // "remove" for multiple-date thingies
    .on("click",".btn-remove-item",function(e){
        e.preventDefault();
        $(this).closest("div").remove();
    }).on("click","#btn-delete",function(e){
        e.preventDefault();
        var type = $("input[name='type']").val().toUpperCase();
        if (!window.confirm(`Are you sure you want to delete this ${type}?`)) {
            return;
        }
        delete_note();
    });
    $("#tabs-notes .nav-link").on("click",function(e){
        e.preventDefault();
        if ($(this).hasClass("active")) {
           //console.debug("this one is active already");
           return;
        }
        console.log("if no MOT[DW], try harder?");
        /**
         * to do here:
         * figure out which tab we're dealing with
         * check whether there is a MOT[DW] loaded
         * if not, try to fetch it via xhr
         * then call show
         */
        $(this).tab("show");
    });

});
