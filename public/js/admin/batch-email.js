/** public/js/admin/batch-email.js */

/* global  $, fail, moment */

$(function(){
    var form = $("#email-form");
    form.carousel();
    $(`button[name="preview"]`).on("click",(e)=>{
        $.post('/admin/email/preview',form.serialize())
        .then((res)=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            }
            form.carousel("next");
        });
    });
    $("#recipient_list").on("change",function(){
        var is_availability_list = $(this).children(":selected").text().includes("availab");
        if (is_availability_list) {
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
            $("#message").text(
                `We write to ask when you would be available to accept contract interpreting assignments for ${organization_name} `
                + `during the coming week of ${from} through ${to}.`);
        } else {
            $("#subject").val("");
            $("#message").text("");
        }
    });

});
