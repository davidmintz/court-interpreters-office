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