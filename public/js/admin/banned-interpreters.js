/** attempting to rewrite banned-interpreter-warning script */
/*
global $, fail, displayValidationErrors, formatDocketElement, parseTime, toggleSelectClass,
event_type_element, judgeElement, interpreterButton, submitterElement
*/

/**
 * [description]
 * @param  {object} e [description]
 * @return {boolean} true if tests are needed
 */
var test_for_banned_is_required = function(e){

    if (e) {
        console.debug(`FYI, target id is ${e.target.id}`);
    }
    // if it's historic, I guess we don't care. so check the date...
    if ($("#date").val()) {
        var when = moment($("#date").val(),"MM/DD/YYYY");
        if (when.isBefore(moment(),"day")) {
            console.debug("date is in the past, so it's moot. returning");
            return false;
        }
    }
    // if event-type is neither in- nor out-of-court, nothing to do.
    var event_category = $("#event_type").children(":selected").data("category");
    if (! ["in","out"].includes(event_category)) {
        console.debug("null|irrelevant relevant event type selected. returning.");
        return false;
    }

    // if we were triggered by a change on submitter or judge
    if (e && ["submitter", "judge"].includes(e.target.id)) {
        // but there are no interpreters yet assigned...
        if (! $("#interpreter-select").val() && ! $("li.interpreter-assigned").length) {
            // there's nothing to test
            console.debug("no interpreters to check, returning false");
            return false;
        } else {
            // it depends.
            if (e.target.id === "judge" && event_category === "in") {
                return true;
            } else if (e.target.id === "submitter" && event_category === "out") {
                return true;
            }
        }
    }
    // if we're triggered by the add-interpreter click event
    if (e && e.target.id === "btn-add-interpreter") {
        if ($("#judge").val() && event_category === "in") {
            return true;
        } else if ($("#submitter").val() && event_category === "out") {
            return true;
        }
    }

    return false;

};
