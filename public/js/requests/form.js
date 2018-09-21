var $, displayValidationErrors, formatDocketElement, parseTime, toggleSelectClass, moment;

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

$(function(){

    const form = $("#request-form");
    const is_update = form.attr("action").indexOf("create") === -1;

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
    $("#defendant-search").autocomplete(deftname_autocomplete_options);
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
});
