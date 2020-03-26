/** public/js/admin/batch-email.js */

/* global  $, fail, moment */


var re =/(\d+)\D+(\d+)/;
var progress_element = $("#progress");
var check_progress = function(response){

    $.get("/admin/email/progress").then(r=>{
        if (typeof r.status === "string") {
            var m = r.status.match(re);
            if (m && m.length === 3) {
                var n = m[1]/m[2];
                var pct = Math.round(n * 100);
                var width = `${pct}%`;
                progress_element.css({width}).attr({"aria-valuenow":pct});
            }
        } else {
            console.warn("WTF");
        }

        if (r.status === "done") {
            progress_element.attr({"aria-valuenow":100}).css({width:"100%"});//.text("100%");
            complete();
            return;
        }
        window.setTimeout(check_progress,250);
    });
}

var complete = function(){
    var form = $("#email-form");
    form[0].reset();
    $("#body").text("");
    $(`button[name="revise"], button[name="send"]`).removeAttr("disabled");
    form.carousel("prev");
    var total = form.data().total;
    // give the progress bar animation time to finish
    window.setTimeout(
        ()=>{$(".alert-success p")
        .text(`Finished sending messages to ${total} recipients.`)
        .parent().removeAttr("hidden").show();
    }, 500);
};
var test = function() {
    var form = $("#email-form");
    $("#recipient_list").val("all active request submitters");
    $("#subject").val("A test of the email broadcasting system");
    $("#body").text("There will be a brief service interruption, after which we will cordially invite you to eat shit and die.\r\n\r\nVery truly yours,\r\n\r\nThe Interpreters");
    $(`button[name="preview"]`).trigger("click");
}
var submit_form = function(e){
    e.preventDefault();
    var form = $("#email-form");
    var data = form.serialize();
    $(`button[name="send"], button[name="revise"]`).attr({disabled : true});
    $("div.progress").show();
    $.post("/admin/email/send",data).then((r)=>{
            form.data({total:r.total});
            check_progress(r);
        }).fail((r)=>{
        if (r.status === 503) {
            $("#error-div h3").text(r.statusText)
            $("#error-message").text(r.responseJSON.message).parent().show();
        } else {
            fail(r);
        }
    });
};

$(function(){
    var form = $("#email-form");
    form.carousel();
    $(`button[name="preview"]`).on("click",(e)=>{
        $.post('/admin/email/preview',form.serialize())
        .then((res)=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            }
            $(".validation-error").hide();
            $("#message-preview").html(res.markdown);
            $("#recipient-preview").text($("#recipient_list").val());
            $("#subject-preview").text($("#subject").val());
            $("#salutation-preview").text($("#salutation option:selected").text());
            form.carousel("next");
        });
    });
    $(`button[name="revise"]`).on("click",(e)=>{form.carousel("prev");});
    $(`button[name="send"]`).on("click",submit_form);
    var list_help = $("#list-help");
    $("#recipient_list").on("change",function(){
        var is_availability_list = $(this).children(":selected").text().includes("availab");
        if (is_availability_list) {
            list_help.html(`To view or modify this list see <a target="_blank" href="${window.basePath}/admin/interpreters/availability/list">${window.basePath}/admin/interpreters/availability/list</a>`  );
            var next_monday = moment().add(1,'weeks').startOf('isoWeek');
            var from = next_monday.format("ddd DD-MMM-YYYY");
            var to = next_monday.add(4,'days').format("ddd DD-MMM-YYYY");
            $("#subject").val(`your availability from ${from} to ${to}`);
            var organization_name = $("#email-form").data("organization_name");
            if (organization_name) {
                organization_name = `the ${organization_name}`;
            } else {
                organization_name = `our office`;
            }            
            // subject to further tweaking etc...
            $("#body").text(
                `We write to ask when you would be available to accept contract interpreting assignments for ${organization_name} `
                + `during the coming week of ${from} through ${to}.`);
        } else {
            $("#subject").val("");
            $("#message").text("");
            list_help.empty();
        }
    });
    //test();
});
