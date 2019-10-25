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
Called when the datepicker is selected. The function receives the selected date as text and the datepicker instance as parameters.
'this' refers to the associated input field.
*/
$(function(){
    var base_url =
    dp_defaults = {
        dateFormat:"yy-mm-dd",
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        onSelect :  function(dateText,instance) {
            // type of note: either 'motd' or 'motw'
            var type = instance.id.substring(9);
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
        console.warn("doing shit: load form for "+`/${path}`);
        var content_div = btn.prev("div");
        $.get(`/${path}`).then(function(html){
            btn.hide();
            content_div.html(html);
        });
    });
    /** form submission handler for edit|create form */
    $("#tab-content-notes").on("click","#notes-form button.btn-success",function(e){
        var form = $("#notes-form");
        e.preventDefault();
        var type = $("input[name='type']").val();
        var id = $(`input[name="id"]`).val();
        var url, method;
        if (id) {
            // update
            console.log("doing an update ");
            url = `/admin/notes/update/${type}/${id}`;
            method = 'PUT';
        } else {
            // create
            console.log("do a create");
            url = `/admin/notes/create/${type}`;
            method = 'POST';
        }
        $.ajax({url, method, data : form.serialize()
        }).then((res)=>{

            if (res.status === "success") {
                // implies a needless http request but it's expedient as hell
                $(`#calendar-${type} a.ui-state-active`).trigger("click");
                // $(`#btn-editor-${type}`).show();
                // form.replaceWith(res[type].content);
                console.warn("it worked");
            }
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
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
    });
    $("#tabs-notes .nav-link").on("click",function(e){
        e.preventDefault();
        if ($(this).hasClass("active")) {
           console.debug("this one is active already");
           return;
        }
        console.log("if no MOT[DW], try harder");
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
