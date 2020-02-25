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

    // if we were triggered by a change on submitter or judge or event-type
    if (e && ["submitter", "judge", "event_type"].includes(e.target.id)) {
        // but there are no interpreters yet assigned...
        if (! $("#interpreter-select").val() && ! $("li.interpreter-assigned").length) {
            // there's nothing to test
            console.debug("no interpreters to check, returning false");
            return false;
        } else {
            // it depends...
            if (event_category === "in" && $("#judge").val() &&  e.target.id !== "submitter") {
                console.debug("in-court: we think true ");
                return true;
            } else if (event_category === "out" && $("#submitter").val() && e.target.id !== "judge") {
                console.debug("out-of-court: we think true");
                return true;
            }
        }
    }
    // otherwise, if we're triggered by the add-interpreter click event
    if (e && e.target.id === "btn-add-interpreter") {
        if ($("#judge").val() && event_category === "in") {
            return true;
        } else if ($("#submitter").val() && event_category === "out") {
            return true;
        }
    }
    return false;
};

/**
 * [description]
 * @return {[type]} [description]
 */
var get_interpreters_having_issues = function(person_id){

}

$(function(){

    $("#event_type, #judge, #submitter").on("change",function(e){
        // only deal with "natural" events, not el.trigger("change")
        if (! e.originalEvent) { return; }
        var target = e.target.id;
        var dbg = `changed ${e.target.id}, need to test for banned?`;
        if (test_for_banned_is_required(e)) {
            console.debug(`${dbg} YES`);
            var person_id, event_category = $("#event_type option:selected").data("category");
            console.debug(`event category is: ${event_category}`);
            if (event_category === "in") {
                person_id = $("#judge").val();
                console.debug("need to check JUDGE: "+person_id);
            } else if (event_category === "out") {
                person_id = $("#submitter").val();
                console.debug("need to check SUBMITTER: "+person_id);
            } else {
                console.log("neither in nor out? why is this happening?");
            }
        } else {
            console.debug(`${dbg} NO`);
        }
    });

    $("#btn-add-interpreter").on("click",function(e){
        var dbg =  "btn clicked. need to test for banned?";
        if (!test_for_banned_is_required(e)) {
            console.debug(`${dbg} NO`);
            return;
        }
        var event_category = $("#event_type option:selected").data("category");
        var el = event_category === "in" ? "judge" : "submitter";
        console.log("need to get #"+el);
     });
     console.log("boink");
});
