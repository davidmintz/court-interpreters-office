/** public/js/common.js
 *
 * common to most if not all pages in the application
 */

/* global  $, jQuery */

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
 * helper function for showing validation error messages
 *
 * @param  {string} element_name
 * @param  {string} message
 * @param  {boolean} verbose
 * @return {void}
 */
const show_error_message = function(element_name, message, verbose){
    var debug = function(message){
        if (verbose) {console.log(message);}
    };
    debug(`show_error_message: element name ${element_name}, message ${message}`);
    var error_div = $(`#error_${element_name}`);
    if (error_div.length) {
        debug("found existing error div");
        return error_div.html(message).show();
    } // else
    var element = $(`#${element_name}`);
    if (! element.length) {
        // try harder
        var selector = "#" + element_name.replace(/([A-Z])/g,"_$1").toLowerCase();
        element = $(selector);
    }
    if (element.length) {
        debug(`found element for ${element_name}, creating div...`);
        $("<div/>").addClass("alert alert-warning validation-error")
            .html(message).attr({id:`error_${element_name}`})
            .insertAfter(element);
    } else {
        debug(`can't figure out where to put error "${message}" for element ${element_name}`);
    }
};

/**
 * displays validation errors on a form
 *
 * @param object validationErrors
 * @param object options
 * @returns void
 */
const displayValidationErrors = function(errors,options,verbose) // eslint-disable-line
{
    var debug = function(message) {// eslint-disable-line 
        if (verbose) { console.log(message);}
    };
    //debug("running NEW edition displayValidationErrors()");
    $(".validation-error").hide().empty();
    var keys = Object.keys(errors);
    keys.forEach((element)=>{
        for (var prop in errors[element]) {
            // debug("within which errors[element][prop] is a: "+typeof errors[element][prop])
            if (typeof errors[element][prop] === "string") {
                // debug(`found error message "${errors[element][prop]}" for element ${element}`);
                show_error_message(element,errors[element][prop]);
            } else {
                //debug("recursing... ");
                displayValidationErrors(errors[element]);
            }
        }
    });
};


/**
 * on xhr failure
 *
 * used in conjunction with view helper
 * module/InterpretersOffice/src/View/Helper/ErrorMessage.php
 *
 */
const fail = function(response) {  // eslint-disable-line
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

$(".alert button[data-hide]").on("click",function(){
    $(this).parent().slideUp();
});

/* cat picture? */
// if (43 === parseInt(Math.random()*1000)) {
//
// }
//
