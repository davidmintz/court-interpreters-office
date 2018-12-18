var $, displayValidationErrors, formatDocketElement, parseTime,
    toggleSelectClass, moment;

/**
 * when they select a name from autocompletion, add it to the form
 */
var appendDefendant = function(data)
{
    var element_name = "request[defendants][]";
    var id = "deft-"+data.value;
    if (data.extra_deftname === true)
    {
        element_name = "request[extra_defendants][]";
        id = "deft-"+moment().valueOf();
    }
    // make sure the guy is not already there
    var duplicate = false;
    $("#defendants li span.deft-name").each(function(){
        console.log($(this).text());
        if ($(this).text().toLowerCase() === data.label.toLowerCase()) {
            duplicate = true;
            return false;
        }
    });
    if (duplicate) {
        return alert("this name has already been added to the form");
    }
    var html =
            `<li id="${id}" class="list-group-item pr-0 py-0">
            <span class="float-left pt-2 deft-name">${data.label}</span>
            <input type="hidden" name="${element_name}" value="${data.value}">
            <button class="btn btn-warning btn-remove-item float-right border" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant</span></button>
            </li>`;
    $("#defendants").append(html);
};

/**
 * datepicker: disable dates less than two weekdays from current date
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
        var slideout =  $("#deft-results-slideout");
        if (slideout.is(":visible")) {
            slideout.hide();
        }
    }
};

$(function(){

    const form = $("#request-form");
    const is_update = form.attr("action").indexOf("create") === -1;
    const defendant_search = $("#defendant-search");
    const defendant_name_form = $("#form-add-deft");
    const slideout = $("#deft-results-slideout");

    const location = $("#location");

    // set default location if possible. TO DO: do this server-side?
    if (! location.val() && $("#judge").val()) {
        location.val(($("#judge :selected").data().default_location));
    }

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
                    // we can do better than this.
                    /** @todo revise displayValidationErrors helper */
                    var untimely = false;
                    if (response.validation_errors.request) {
                        // look for key lessThanTwoBusinessDaysNotice,
                        // which has no corresponding form element or div
                        var errors = response.validation_errors.request;
                        if (errors.date && errors.date.lessThanTwoBusinessDaysNotice) {
                            untimely = true;
                            $("#error_insufficient_notice")
                                .text(new String(errors.date.lessThanTwoBusinessDaysNotice));
                            delete errors.date.lessThanTwoBusinessDaysNotice;
                        }
                        displayValidationErrors(response.validation_errors.request);
                        delete response.validation_errors.request;
                    }
                    // some might be outside the  '.request' property
                    if (Object.keys(response.validation_errors).length) {
                        displayValidationErrors(response.validation_errors);
                    }
                    if (untimely) {
                        // this sucks: undo the .hide() in displayValidationErrors
                        $("#error_insufficient_notice").show();
                    }
                    console.log("shit failed validation");
                    return;
                }
                return document.location = `${window.basePath}/requests/list`;

            }).fail(fail);
    });

    /**
     * if they can't or don't want to find the name through autocompletion,
     * clicking the search icon fetches results, if any, otherwise display
     * a form for adding arbitrary names
     */
    $("#btn-deft-search").on("click",function(event)
    {
        event.preventDefault();
        var term = defendant_search.val();
        if (term.length < 2) {
            return window.alert("please type at least two letters of the surname");
        }
        $.get('/defendants/search',{term:term})
            .done((response)=>{
                $("#deft-results").html(response);
                if (0 === $("#deft-results ul.defendant-names").length) {
                    defendant_name_form.show();
                    $("#btn-show-deft-form").hide();
                } else {
                    $("#form-add-deft .validation-error").hide();
                    defendant_name_form.hide();
                    $("#btn-show-deft-form").show();
                }
                // these might have gotten hidden
                $("#deft-results").show();
                slideout.toggle("slide");
            })
            .fail(fail);
    });

    /**
     * when they click a name in the search results, add it to the form
     */
    slideout.on("click","li.list-group-item a",(event)=>{
        event.preventDefault();
        var element = $(event.target);
        var value = element.parent().data("id");
        var label = element.text().trim();
        appendDefendant({value, label});
        slideout.toggle("slide");
        defendant_search.val("");
    });

    /** close button for dismissing deft-results */
    $("#deft-results-slideout button.close").on("click",
        () => slideout.toggle("slide")
    );

    /** button to display the form for arbitrary names */
    $("#btn-show-deft-form").on("click",function(event){
        event.preventDefault();
        $("#deft-results, #btn-show-deft-form").slideUp();
        defendant_name_form.show();
    });

    /** defendant search result: pagination links ========================*/
    slideout.on("click",".pagination a",function(event){
        event.preventDefault();
        $("#deft-results").load(this.href);
    });

    /** add a new(ish) name to the request form */
    $("#btn-add-new").on("click",function(event)
    {
        event.preventDefault();
        // validate
        $.post("/defendants/validate",defendant_name_form.serialize())
            .done((response)=>{
                if (! response.valid) {
                    return displayValidationErrors(response.validation_errors);
                }
                // otherwise, add it as a "special" thing
                var label = $("#surnames").val().trim()+ ", "
                    + $("#given_names").val().trim();
                appendDefendant({ label, value : label, extra_deftname : true});
                defendant_search.val("");
                $("#form-add-deft")[0].reset();
                slideout.toggle("slide");
            }).fail(fail);
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
