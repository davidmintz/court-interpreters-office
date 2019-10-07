/** public/js/common.js
 *
 * code that is common to most if not all pages in the application
 */


var $, jQuery;
var basePath;
/**
 * redirect to login page if, e.g., session has timed out
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
    var debug = function(message) {
        if (! options || !options.debug) { return; }
        console.log(message);
    }
    $(".validation-error").hide().empty();
    debug("running displayValidationErrors()");
    //var debug = (options && options.debug) || false;
    for (var field in validationErrors) {
        debug("looking at field: "+field);
        for (var key in validationErrors[field]) {
            debug("looking at key: "+key);
            //if (debug) { console.log("looking at: "+key); }
            var message = validationErrors[field][key];
            if (typeof message === "object") {
                debug("message happens to be an object; recursing"); ;
                return displayValidationErrors(validationErrors[field],options);
            }
            var element = $("#" +field);
            if (! element.length) {
                // nothing to lose by trying harder; undo camelcase
                var id = "#" + field.replace(/([A-Z])/g,"_$1").toLowerCase();
                element = $(id);
            }
            var errorDiv = $("#error_"+field);
            debug(`error div? ${errorDiv.length}`);
            if (! errorDiv.length) { errorDiv = null;}
            if (! element.length) {
                debug("is there no element "+field+ " ?"); ;
                // look for an existing div by id
                if ($("#error_"+field).length) {
                    $("#error_"+field).html(message).show();
                } else {
                    debug(`'message' is of type ${typeof message};`);
                    debug(`no element with id ${field}and nowhere to put message: "${message}"`);
                }
            } else { // yes, there is an element for inserting error
                errorDiv = errorDiv || element.next(".validation-error");
                if (! errorDiv.length) {
                    debug("creating a div for validation error");
                    errorDiv = $("<div/>")
                        .addClass("alert alert-warning validation-error")
                        .attr({id:"error_"+field})
                        .insertAfter(element);
                } else {
                    debug("found existing div for validation error");
                }
                debug("putting shit in there and showing");
                errorDiv.html(message).show();
            }
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
        msg = response.responseJSON.message;
    } else {
        msg = `<p>Sorry &mdash; a system error happened while
        processing your last request. If the problem recurs, please notify your site
        administrator for assistance.</p><p>We apologize for the inconvenience.</p>`;
    }
    $(".alert-success").hide();
    $("#error-message").html(msg).parent().show();
    // for development...
    //$("html").html(response.responseText);
    console.log(response.responseText);
};

$(".alert button[data-hide]").on("click",function(e){
    $(this).parent().slideUp();
});
