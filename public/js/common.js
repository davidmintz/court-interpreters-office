/** public/js/common.js
 *
 * code that is common to most if not all pages in the application
 */


var $, jQuery;
var basePath;
/**
 * redirect to login page if, e.g., session has timed out
 * note that this does NOT work well with xhr
 */
$( document ).ajaxComplete(function(event, xhr) {
    if (xhr.getResponseHeader("X-Authentication-required")) {
        document.location = (basePath || "/") + "login";
    }
});

/** experimental: prepend basePath if such exists */
jQuery.ajaxSetup({
    beforeSend : function(xhr,settings) {
        if (window.basePath && window.basePath.length
            && settings.url.indexOf(window.basePath) !== 0
            ) {
            settings.url = window.basePath + settings.url;
        }
    }
});

/**
 * displays validation errors on a form
 *
 * @param object validationErrors
 * @param object options
 * @returns void
 */
var displayValidationErrors = function(validationErrors,options) {
    $(".validation-error").hide();
    var debug = (options && options.debug) || false;
    for (var field in validationErrors) {
        if (debug) { console.log("looking at: "+field); }
        for (var key in validationErrors[field]) {
            if (debug) { console.log("looking at: "+key); }
            var message = validationErrors[field][key];
            var element = $("#" +field);
            if (! element.length) {
                // nothing to lose by trying harder; undo camelcase
                var id = "#" + field.replace(/([A-Z])/g,"_$1").toLowerCase();
                element = $(id);
            }
            var errorDiv = $("#error_"+field);
            if (! errorDiv.length) { errorDiv = null;}
            if (! element.length) {
                if (debug) { console.log("is there no element "+field+ " ?"); }
                // look for an existing div by id
                if ($("#error_"+field).length) {
                    $("#error_"+field).html(message).show();
                } else {
                    if (debug) {
                        console.log(`'message' is of type ${typeof message}`);
                        console.warn("no element with id "+field
                            + ", and nowhere to put message: "+message);
                    }
                }
            } else { // yes, there is an element for inserting error
                errorDiv = errorDiv || element.next(".validation-error");
                if (! errorDiv.length) {
                    errorDiv = $("<div/>")
                        .addClass("alert alert-warning validation-error")
                        .attr({id:"error_"+field})
                        .insertAfter(element);
                }
                errorDiv.html(message).show();
            }
            //break;
        }
    }
};

/**
 * on xhr failure
 *
 * used in conjunction with view helper
 * module/InterpretersOffice/src/View/Helper/ErrorMessage.php
 *
 */
const fail = function(response) {
    var msg;
    if (response.status === 403 && response.responseJSON) {
        msg = response.responseJSON.error.message;
    } else {
        msg = `<p>Sorry &mdash; we encountered an unexpected system error while
        processing your last request. If the problem recurs, please notify your site
        administrator for assistance.</p><p>We apologize for the inconvenience.</p>`;
    }
    $("#error-message").html(msg).parent().show();
    console.log(response.responseText);
    $("html").html(response.responseText);
};

$("button[data-hide]").on("click",function(e){
    $(this).parent().slideUp();
});

// bullshit...
var __displayValidationErrors = function(validationErrors, options)
{
    // Object.keys(errors).forEach(function(key){
    //     console.log(typeof errors[key]);
    //     if ("object" === typeof errors[key]) {
    //         console.log("huh?");
    //         Object.keys(errors[key]).forEach(function(key){
    //             console.log("key is now: "+key)
    //         })
    //     }
    // })
    var debug = (options && options.debug) || false;
    for (var name in validationErrors) {
    }
    return { foo: { bar: "doink"}, baz : "gack"};
}
