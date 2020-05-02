/** public/js/admin/batch-email.js */

/* global  $, fail, moment, displayValidationErrors */
var submit_form = function(e){
    console.log("loading the mailgun");
    e.preventDefault();
    var form = $("#email-form");
    var data = form.serialize();
    $("button[name=\"send\"], button[name=\"revise\"]").attr({disabled : true});
    // display a wait thing
    $.post("/admin/email/mailgun",data)
        .then((res)=>{
            // dispay a confirmation
            console.log("got a response");
            console.log(res);
        });   
};

$(function(){
    console.log("shit is real");
    
    /** yes, this section is the same as public/js/admin/batch-email.js */
    var form = $("#email-form");
    form.carousel();
    $("button[name=\"preview\"]").on("click",(e)=>{
        $.post("/admin/email/preview",form.serialize())
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
    $("button[name=\"revise\"]").on("click",(e)=>{form.carousel("prev");});
    $("button[name=\"send\"]").on("click",submit_form);
    var list_help = $("#list-help");
    $("#recipient_list").on("change",function(){
        var is_availability_list = $(this).children(":selected").text().includes("availab");
        if (is_availability_list) {
            list_help.html(`To view or modify this list see <a target="_blank" href="${window.basePath}/admin/interpreters/availability/list">${window.basePath}/admin/interpreters/availability/list</a>`  );
            var next_monday = moment().add(1,"weeks").startOf("isoWeek");
            var from = next_monday.format("ddd DD-MMM-YYYY");
            var to = next_monday.add(4,"days").format("ddd DD-MMM-YYYY");
            $("#subject").val(`your availability from ${from} to ${to}`);
            var organization_name = $("#email-form").data("organization_name");
            if (organization_name) {
                organization_name = `the ${organization_name}`;
            } else {
                organization_name = "our office";
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
});

/* eslint no-unused-vars: off */
var test = function() {      
    $("#recipient_list").val("test recipients");
    $("#subject").val("A test of the email broadcasting system");
    $("#body").text("This is a test of the SDNY interpreters batch email feature. If you were not expecting to receive this, then it's a mistake. Our apologies for the intrusion.\r\n\r\nVery truly yours,\r\n\r\nThe Interpreters");
    $("button[name=\"preview\"]").trigger("click");
};

