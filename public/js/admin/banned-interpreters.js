/** banned-interpreter-warning for event form */
/* global $ */

/**
 * tests whether to bother checking for banned-interpreter issues
 * @param  {object} e
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
        // console.debug("null|irrelevant relevant event type selected. returning.");
        return false;
    }

    // if we were triggered by a change on submitter or judge or event-type
    if (e && ["submitter", "judge", "event_type"].includes(e.target.id)) {
        // but there are no interpreters yet assigned...
        if (! $("#interpreter-select").val() && ! $("li.interpreter-assigned").length) {
            // there's nothing to test
            //console.debug("no interpreters to check, returning false");
            return false;
        }
        // else, it depends...
        if (event_category === "in" && $("#judge").val() &&  e.target.id !== "submitter") {
            console.debug("in-court: we think true ");
            return true;
        } else if (event_category === "out" && $("#submitter").val() && e.target.id !== "judge") {
            console.debug("out-of-court: we think true");
            return true;
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
 * returns elements filtered for interpreters banned by person_id
 * @return {object} jQuery
 */
var get_banned_interpreter_elements = function(person_id){
    return $("li.interpreter-assigned, #interpreter-select option:selected")
        .filter(function(){
            var el = $(this);
            console.debug(`get_banned... filter looking at a ${this.tagName}`);
            if (! el.data("banned_by")) {
                return false;
            }
            return el.data("banned_by").toString().split(",").includes(person_id);
    });
};

/**
 * returns string of interpreter name(s) for warning message
 * @return {string}
 */
var parse_names_from_elements = function(elements) {

    var names = [];
    elements.each(function(){
        if (this.tagName === "LI") {
            names.push($(this).children("span").text().trim().replace(/(.+), +(.+)/,"$2 $1"));
        } else {
            names.push($(this).text().trim().replace(/(.+), +(.+)/,"$2 $1"));
        }
    });
    var verbiage = "the interpreter";
    switch (names.length) {
        case 1:
          verbiage += ` ${names[0]}`;
          break;
        case 2:
          verbiage += `s ${names.join(" and ")}`;
          break;
        default:
        // improbable, but hey you never know...
          var str = names.join(", ").replace(/(.+)(, )(.+)$/, "$1 and $3");
          verbiage += `s ${str}`;
    }

    return verbiage;
};

/**
 * returns text for warning message
 * @param {string} names
 * @param {string} event_category
 * @return {string}
 */
var compose_warning_message = function(names, event_category, target_id) {
    var text;
    if (event_category === "in") {
        text = `Your records indicate that ${names} should not be assigned to matters before this judge.`;
    } else if (event_category === "out") {
        var whom = $("#submitter option:selected").text().trim().replace(/(.+), +(.+)/,"$2 $1");
        text = `Your records indicate that ${names} should not be assigned to events requested by ${whom}.`;
    }
    if (target_id === "btn-add-interpreter") {
        text += " Continue anyway?";
    } else {
        text += " Remove?";
    }
    return text;
};
/**
 * composes and displays modal warning about banned interpreters
 * @param {jQuery} banned
 * @param {string} event_category
 * @param {string} target_id
 * @return void
 */
var show_banned_warning = function(banned, event_category, target_id)
{
    var modal = $("#modal-assign-interpreter");
    var modal_body = $("#modal-assign-interpreter .modal-body");
    var btn_yes = $("#btn-yes-assign-interpreter");
    var btn_no = $("#btn-no-assign-interpreter");
    var names = parse_names_from_elements(banned);
    var text = compose_warning_message(names, event_category, target_id);
    var handler = function(e){
        // figure out the elements again, because when show_banned_warning()
        // first runs, the elements to remove might not all be LI elements yet.
        var person_id = event_category == "in" ? $("#judge").val() : $("#submitter").val();
        var banned = get_banned_interpreter_elements(person_id)
            .filter(function(){return this.tagName === "LI"});
        console.log(`handler has ${banned.length} elements`);
        banned.remove();
    };
    // which button to attach to depends on how the question was posed
    if (target_id === "btn-add-interpreter") {
        btn_no.one("click",handler);
    } else {
        btn_yes.one("click",handler);
    }
    modal_body.text(text);
    modal.on("hide.bs.modal",function(){modal.off("click","button",handler);});
    modal.modal("show");
};

$(function(){
    // inject the banned_by data to any already-assigned interpreter <li>
    let banned = $("#interpreter-select").children().filter(
        function() { return $(this).data("banned_by")}
    );
    if (banned.length && $("li.interpreter-assigned").length) {
        banned.each(function(){
            var interpreter_id = $(this).attr("value");
            var el = $(`li.interpreter-assigned input[name$="[interpreter]"][value="${interpreter_id}"`);
            if (el.length) {
                el.closest("li").data($(this).data());
            }
        });
    }
    $("#event_type, #judge, #submitter").on("change",function(e){
        // only deal with "natural" events, not el.trigger("change")
        if (! e.originalEvent) { return; }
        if (test_for_banned_is_required(e)) {
            var person_id, event_category = $("#event_type option:selected").data("category");
            if (event_category === "in") {
                person_id = $("#judge").val();
                // console.debug("need to check JUDGE: "+person_id);
            } else if (event_category === "out") {
                person_id = $("#submitter").val();
                // console.debug("need to check SUBMITTER: "+person_id);
            } else {
                console.log("neither in nor out? why is this happening?");
            }
            var banned = get_banned_interpreter_elements(person_id);
            if (banned.length) {
                show_banned_warning(banned, event_category, e.target.id);
            }
        }
    });

    $("#btn-add-interpreter").on("click",function(e){
        if (!test_for_banned_is_required(e)) {
            console.debug(`NO test-for-banned required, returning`);
            return;
        }
        var event_category = $("#event_type option:selected").data("category");
        var el = event_category === "in" ? "judge" : "submitter";
        var person_id = $(`#${el}`).val();
        var banned = get_banned_interpreter_elements(person_id);
        if (! banned.length) {
            console.debug("no interpreters with bad baggage found");
        } else {
            console.debug(`${banned.length} banned elements found`);
            show_banned_warning(banned, event_category, e.target.id);
        }
     });
});
