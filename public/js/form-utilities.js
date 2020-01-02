/**
 * tries to figure out time of day based on string
 * @param  {object} event
 * @return {void}
 */
var parseTime = function(event)
{
    var timeElement = $(event.target);
    var div = timeElement.closest("div.form-group");
    var errorDiv = timeElement.next(".validation-error");
    if (! errorDiv.length) {
        timeElement.after($("<div>").addClass("alert alert-warning validation-error"));
        errorDiv = timeElement.next(".validation-error");
    }
    var time = timeElement.val().trim();
    if ("" === time) {
        return;
    }
    var re = /^(0?[1-9]|1[0-2]):?([0-5]\d)?\s*((a|p)m?)?$/i;
    var hour; var minute; var ap;
    var matches = time.match(re);
    if (matches)
    {
        hour = matches[1];
        if ("0" === hour[0]) { // no leading zero
            hour = hour.substring(1);
        }
        minute = matches[2] ? matches[2] : "00";
        ap = matches[3];
        if (!ap) {
            if (hour === "12") {
                ap = "pm";
            } else {
                ap = hour < 9 ? "pm" : "am";
            }
        } else if (ap.length === 1) {
            ap = ap + "m";
        }
    } else {
        matches = time.match(/^([01][0-9]|2[1-3])([0-5][0-9])$/);
        if (matches) {
            hour = matches[1];
            ap = "am";
            if (hour > 12) {
                hour -= 12;
                ap = "pm";
            }
            minute = matches[2];
        }
    }
    if (! matches) {
        errorDiv.addClass("alert alert-warning validation-error")
            .text("invalid time").show();
        return;
    }
    div.removeClass("alert alert-warning validation-error");
    errorDiv.empty().hide();
    timeElement.val((hour + ":" + minute + " " + ap).toLowerCase());
};

/**
 * regular expression for docket numbers
 * @type {object} regular expression
 */
var DocketRegExp = /^(?:s-?[1-9] *)?((?:19|20)?\d{2})[- .]*(c(?:r(?:im)?|i?v)|m(?:ag|j)?)[- .]*(\d{1,5})(?: *\([a-z]{2,3}\))?$/i;
/**
 * tries to format a string as a US District Court docket number
 * @param  {object} event
 * @return {jQuery} event's target element
 */
var formatDocketElement = function(event)
{
    var element = $(event.target);
    var div = element.closest("div.form-group");
    var errorDiv = element.next(".validation-error");
    if (! errorDiv.length) {
        // try something else
        errorDiv = element.parent().next(".validation-error");
        if (! errorDiv.length)  {
            // last resort
            element.after($("<div>").addClass("alert alert-warning validation-error"));
            errorDiv = element.next(".validation-error");
        }
    }
    element.val(element.val().trim());
    if (! element[0].value ) {
        errorDiv.empty().hide();
        element.data("valid",1);
        return element;
    }
    var matches = element[0].value.match(DocketRegExp);
    if (element[0].value && ! matches) {
        errorDiv.text("invalid docket number").show().trigger("show");
        element.data("valid",0);
        div.addClass("has-error has-feedback");
        return;

    } else {
        div.removeClass("has-error has-feedback");
        var year = matches[1];
        var flavor = matches[2];
        var number = matches[3];
    }
    if (year.length === 2) {
        year = year <= 50 ? "20"+year : "19"+year;
    }
    flavor = flavor.toUpperCase();
    if (-1 !== flavor.indexOf("CR")) {
        flavor = "CR";
    } else if (flavor[0] === "M") {
        flavor = "MAG";
    } else {
        flavor = "CIV";
    }
    if (number.length < 4) {
        var padding = new Array(5 - number.length).join("0");
        number = padding + number;
    } else if (number.length === 5) {
        // four digits with up to three leading zeroes is enough
        number = number.replace(/^00/,"0");
    }
    element.val(year + "-"  + flavor + "-" + number)
        .data("valid",1);
    errorDiv.empty().hide();

    return element;
};

var toggleSelectClass = function(event) {
    var element = $(this);
    if (element.val()) {
        element.removeClass("text-muted");
    } else {
        element.addClass("text-muted");
    }
}

const append_date = function(date,namespace){
    var m = moment(date,"MM/DD/YYYY");
    var value = m.format("YYYY-MM-DD");
    if ($(`#dates input[value="${value}"]`).length) {
        return;
    }
    var display = m.format("ddd DD-MMM-YYYY");
    $("#dates .form-text").hide();
    $("#dates .list-group").append(
        `<li style="font-size:90%;font-family:monospace" class="list-group-item pl-2 pr-1 py-1 multidate">
        <span class="float-left pt-1 align-middle">${display}</span>
        <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this date">
        <span class="fas fa-times" aria-hidden="true"></span>
        <span class="sr-only">remove this date</span></button>
        <input type="hidden" name="${namespace}[dates][]" value="${value}">
        </li>`
    );
    var sorted = $("ul.list-group li.multidate").sort((a,b)=>{
        var date1 = $(a).children("input").val();
        var date2 = $(b).children("input").val();
        if (date1 == date2) { return 0; } // should not be necessary
        return date1 > date2 ? 1 : -1; });
    $("#dates .list-group").html(sorted);
};
const enable_multidate = function(namespace,options)
{
    console.log("enabling multi-date");
    $("form").on("click","li.multidate .btn-remove-item",(event)=>event.preventDefault());
    var div_exists = $("#div-multi-dates").length > 0;
    var date_element = $("#date");
    widths = {
        event : { label : 2, dates : 4, calendar : 4
        },
        request : {label : 3, dates : 4, calendar : 5},
    };
    if (! div_exists) {
        var form_row = options.appendAfter || date_element.closest("div.form-row");
        form_row.after(
        `<div class="form-group form-row" style="display:none" id="div-multi-dates" >
            <label for="dates" class="col-form-label col-sm-${widths[namespace].label} pr-1">dates</label>
            <div id="dates" class="col-sm-${widths[namespace].dates}">
                <p class="form-text text-muted mt-2">please select your dates</p>
                <ul class="list-group"></ul>
                <div id="error_dates" class="alert alert-warning validation-error" style="display:none"></div>
            </div>
            <div id="cal-multi-dates" class="col-sm-${widths[namespace].calendar}"></div>
        </div>`
        );
        var opts = Object.assign(options.datepicker,{onSelect: function(date){append_date(date,namespace);}})
        $("#cal-multi-dates").datepicker(opts);
    }
    $("#div-multi-dates").slideDown();
    $("#date").attr({disabled:true});
};

const disable_multidate = function(){
    console.log("disable multi-date");
    $("#div-multi-dates").slideUp();
    $("#date").attr({disabled:false});
};

// maybe keep this here?
$("form").on("click","li .btn-remove-item",function(e){
    e.preventDefault();
    $(this).closest("li").slideUp(function(){$(this).remove()});
});
