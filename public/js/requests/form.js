var $, displayValidationErrors, formatDocketElement, parseTime,
    toggleSelectClass, moment;

/**
 * when they select a name from autocompletion, add it to the form
 */
var appendDefendant = function(data)
{
    var html =
            `<li id="deft-${data.value}" class="list-group-item pr-0 py-0">
            <span class="float-left pt-2">${data.label}</span>
            <input type="hidden" name="request[defendants][]" value="${data.value}">
            <button class="btn btn-warning btn-remove-item float-right border" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant
            </span></button>
            </li>`;
    $("#defendants").append(html);
};

/**
 * datepicker: disable dates less than two week days from current date
 */
var minDate;
switch (moment().day()) {
    case 0:
        // Sunday: next shot is Wednesday
        minDate = "+3";
        break;
    case 5:
    case 6:
        minDate = "+4";
        break;
    default :
        minDate = "+2";
}

const datepicker_options = {
    changeMonth: true,
    changeYear: true,
    selectOtherMonths : true,
    showOtherMonths : true,
    minDate : minDate
};

/** autocompletion options for defendant name search */
const deftname_autocomplete_options = {
    source: "/defendants/autocomplete",
    minLength: 2,
    select: (event, ui) => {
        event.preventDefault();
        appendDefendant(ui.item);
        $("#defendant-search").val("");
    },
    focus: function(event,ui) {
        event.preventDefault();
        $(this).val(ui.item.label);
    },
    open : function() {
        // if (slideout.is(":visible")) {
        //     slideout.hide();
        // }
    }
};

/** error handler */
const fail = function(response){
    var html = `<p>Sorry, we've encountered an unexpected error.`;
    if (response.responseJSON && response.responseJSON.message) {
        html += ` The message was: ${response.responseJSON.message}`;
    }
    html += `</p><p>Please consult your site administrator for help</p>`;
    $("#error-message").html(html).show();
};

$(function(){

    const form = $("#request-form");
    const is_update = form.attr("action").indexOf("create") === -1;
    const defendant_search = $("#defendant-search");
    defendant_search.autocomplete(deftname_autocomplete_options);
    $("#time").on("change",parseTime);
    $("#date").datepicker(datepicker_options);
    $("#docket").on("change",formatDocketElement);
    $("#defendants").on("click",".btn-remove-item", function(event)
    {
        event.preventDefault();
        $(this).closest(".list-group-item").slideUp(
            function(){ $(this).remove();} );
        }
    );

    $("select").on("change",toggleSelectClass).trigger("change");
    const help_button = $("#btn-help-deft-search")
    const help_text = $("#help-defendant-search");
    help_button.on("click",() => help_text.slideToggle());
    help_text.children("button.close")
        .on("click",()=>help_button.trigger("click"));

    $("#btn-save").on("click",function(event){
        event.preventDefault();
        var post = form.serialize();
        $.post(form.attr("action"),post)
            .done(function(response){
                if (response.validation_errors) {
                    // we can do better than this
                    if (response.validation_errors.request) {
                        displayValidationErrors(response.validation_errors.request);
                        delete response.validation_errors.request;
                    }
                    // some might be outside the  '.request' property
                    if (Object.keys(response.validation_errors).length) {
                        displayValidationErrors(response.validation_errors);
                    }
                    return;
                }
                document.location = `${window.basePath}/requests/list`;

            }).fail(fail);
    });

    $("#btn-deft-search").on("click",function(){
        var term = defendant_search.val();
        if (term.length < 2) {
            return window.alert("please type at least two letters of the surname");
        }
        $.get('/defendants/search',{term:term})
            .done((response)=>{console.log("got a response");})
            .fail(fail);
    });
});



var stuff = function()
{
    $("#date").val("09/27/2018");
    $("#time").val("10:00 am");
    $("#eventType").val("16");
    $("#docket").val("2018 CR 123");
    $("#language").val(62);
    appendDefendant({
        label : "Rodriguez, Nelson", value: 9082
    });

}
