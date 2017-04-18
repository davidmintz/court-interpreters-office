/** public/js/common.js 
 * 
 * code that is common to most if not all pages in the application
 */

/**
 * redirect to login page if, e.g.,  session has timed out
 */
$( document ).ajaxComplete(function(event, xhr) {
    if (xhr.getResponseHeader('X-Authentication-required')) {
        document.location = (basePath || "/") + 'login';
        //var doc = $(xhr.responseText);
    }
});

$(document).ready(function(){
    // because the HTML5 validator complains if the date element's value attribute
    // is formatted other than YYYY-mm-dd
    $('input.date').each(function(i,element){
        if (element.value.match(/\d{4}-\d\d-\d\d/)) {
            element.value = element.value.replace(/(\d{4})-(\d\d)-(\d\d)/,"$2/$3/$1");
        }
    });
});