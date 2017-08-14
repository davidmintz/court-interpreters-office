/**
 * public/js/event-form.js
 */

moment = window.moment; 
Modernizr = window.Modernizr;

$(document).ready(
    function() {
        if (! Modernizr.inputtypes.date) {
            $('input.date').datepicker({
                changeMonth: true,
                changeYear: true
            });
            $('input.date').each(function(i,element){
                if (element.value.match(/^\d{4}-\d\d-\d\d$/)) {
                    element.value = element.value.replace(/(\d{4})-(\d\d)-(\d\d)/,"$2/$3/$1");
                }
            });
        }
        if (! Modernizr.inputtypes.time) {
            $("input.time")
              .each(function(){formatTimeElement($(this));})
              .on("change",parseTime);
        }
        $('input.docket').on("change",formatDocketElement);
    }
);
formatTimeElement = function(timeElement) {
    
    var timeValue = timeElement.val();
    // reformat time
    if (timeValue && timeValue.match(/^\d\d:\d\d:\d\d$/)) {
        var formatted = moment(timeValue, 'HH:mm:ss').format('h:mm a');
        console.log('formatted time is: '+formatted);
        timeElement.val(formatted);
    }
    return timeElement;
};

parseTime = function(event)
{
    var timeElement = $(event.target);
    var div = timeElement.closest('div.form-group');
    var errorDiv = timeElement.next('.validation-error');
    if (! errorDiv.length) {
        timeElement.after($("<div>").addClass('validation-error'));
        errorDiv = timeElement.next('.validation-error');
    }
    var time = timeElement.val().trim();
    if ("" === time) {
        return;
    }
    var re = /^(0?[1-9]|1[0-2]):?([0-5]\d)? *((a|p)m?)?$/i;
    var hour, minute, ap;
    matches = time.match(re);
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
            ap = ap + 'm';
        }
    } else if (matches = time.match(/^([01][0-9]|2[1-3])([0-5][0-9])$/)) {
        //console.log(" found 24 hr format");
        hour = matches[1];
        ap = 'am';
        if (hour > 12) {
            hour -= 12;
            ap = 'pm';
        }
        minute = matches[2];
    } else {
        errorDiv.text("invalid time format").show();
        div.addClass('has-error has-feedback');
        return;
    }
    div.removeClass('has-error has-feedback');
    errorDiv.empty().hide();
    timeElement.val((hour + ":" + minute + " " + ap).toLowerCase());
};

formatDocketElement = function(event)
{
    element = $(event.target);
    var div = element.closest('div.form-group');
    var errorDiv = element.next('.validation-error');
    if (! errorDiv.length) {
        // try something else
        errorDiv = $('#docket').parent().next('.validation-error');
        if (! errorDiv.length)  {
            // last resort
            element.after($("<div>").addClass('validation-error'));
            errorDiv = element.next('.validation-error');
        }
    }
    element.val(element.val().trim());
    if (! element[0].value ) { 
        errorDiv.empty().hide();
        element.data('valid',1);
        return element;
    }
    matches = element[0].value.match(DocketRegExp);
    if (element[0].value && ! matches) { 
        errorDiv.text("invalid docket number").show().trigger("show");
        element.data('valid',0);
        return div.addClass('has-error has-feedback');

    } else {
        div.removeClass('has-error has-feedback');
        var year = matches[1];
        var flavor = matches[2];
        var number = matches[3];
    }
    if (year.length === 2) {
        year = year <= 50 ? "20"+year : "19"+year;
    }
    flavor = flavor.toUpperCase();
    if (-1 !== flavor.indexOf('CR')) {
        flavor = 'CR';
    } else if (flavor[0] === 'M') {
        flavor = 'MAG';
    } else {
        flavor = 'CIV';
    }
    if (number.length < 4) {
        var padding = new Array(5 - number.length).join("0");
        number = padding + number;
    }
    element.val(year + '-'  + flavor + '-' + number)
            .data('valid',1);
    errorDiv.empty().hide();
    return element;  
};

