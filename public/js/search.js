var $, formatDocketElement, displayValidationErrors, moment;

const timestamp_format = "YYYY-MM-DD HH:mm:ss";
const seconds = 30;

/** autocompletion options for defendant name search */
const deft_autocomplete_opts = {
    source: "/defendants/autocomplete",
    minLength: 2,
    select: (event, ui) => {
        event.preventDefault();
        $("#defendant-search").val("");
    },
    focus: function(event,ui) {
        event.preventDefault();
        $(this).val(ui.item.label);
    }
};
const init = function(user){
    var rows = $("#results table tbody tr");
    if (! rows.length) {
        return;
    }
    // on page load, deadline is set to 2 business days from now.
    // anything scheduled BEFORE that datetime cannot be updated
    var deadline = new moment($("#results table").data("deadline"),timestamp_format);
    rows.each(function(){
        var tr = $(this);
        var editable = update_is_allowed(user,tr,deadline);
        tr.data({editable});
    });
}
/**
 * is user allowed write access to entity represented by table row?
 * @param  {object} user
 * @param  {jQuery} row
 * @param {object} Moment
 * @return {boolean}
 */
const update_is_allowed = function (user, row, deadline)
{
    //console.debug(row.text());
    var td = row.children("td").slice(0,2).get();
    var str = td.map(x => x.textContent.trim()).join(" ");
    var scheduled_datetime = new moment(str, "DD-MMM-Y h:mm a");
    if (scheduled_datetime.isSameOrBefore(deadline)) {
        //console.debug("returning false: too late");
        return false;
    }
    var request = row.data();
    if (user.is_judge_staff) {
        // it has to be in-court AND belong to one of user's judges
        if (request.category !== "in") {
            //console.debug("returning false: user is judge-staff, event is out");
            return false;
        }
        if (! user.judge_ids.includes(request.judge_id)) {
            //console.debug(`returning false: judge id ${request.judge_id} not among user's judges`);
            return false;
        }
    } else {
        // it has to be owned by them, AND be out-of-court
        // (in case of a change of hat)
        if (request.category !== "out") {
            // console.debug("returning false: user is non-judge-staff, event is in-court");
            return false;
        }
        if (request.submitter_id !== user.person_id) {
            // console.debug("returning false: out-of-court event is owned by someone else");
            return false;
        }
    }
    // console.log("returning true: DONE");
    return true;
}
$(function(){
    init(window.user);
    $("input.docket").on("change",formatDocketElement);
    $("input.date").datepicker({});
    $("#defendant-name").autocomplete(deft_autocomplete_opts)
    var btn = $("#btn-submit");
    var form = btn.closest("form");
    form.append($("<input>").attr({type:"hidden",name:"pseudo_judge",id:"pseudo_judge"}));
    var judge = $("#judge");
    judge.on("change",()=>{
        if (judge.val()) {
            $("#pseudo_judge").val(
                judge.children(":selected").data("pseudojudge") ? 1 : 0
            );
        }
    }).trigger("change");
    $("#btn-submit").on("click",function(e){
        e.preventDefault();
        $.get(form.attr("action"),form.serialize())
        .done((res, status, xhr)=>{
            $(".validation-error").hide();
            if ( xhr.responseJSON && !res.valid ) {
                return displayValidationErrors(res.validation_errors);
            }
            $("#results").html(res);
            init(window.user);
        })
        .fail(fail);
    });
    $("#results").on("click", ".pagination a",function(e){
        e.preventDefault();
        var page, m = this.href.match(/page=(\d+)/);
        if (m && m[1]) {
            page = m[1];
        } else {
            page = 1;
        }
        var path = window.document.location.pathname;
        $.get(`${path}?${form.serialize()}&page=${page}`)
        .done(function(html){
            $("#results").html(html);
            init(window.user);
        })
        .fail(fail);
    });
});
