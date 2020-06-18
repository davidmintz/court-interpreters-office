/**  public/js/schedule.js */
/* global $, moment,fail */
/* eslint-disable no-console */

/**
 * intervals for refreshing schedule, in seconds
 *
 * If the date being displayed is equal or later than the current date, the
 * schedule is considered "current" and we refresh it at frequent intervals.
 * Otherwise, it's historical and there's no need to reload so often.
 *
 * @type {Object}
 */
const schedule_refresh_intervals = { current : 20, historical : 240 };

/**
 * returns HTML for interpreter-editing popover
 *
 * @return {string}
 */
const renderInterpreterEditor = function(){

    var assigned = $(this).parent().prev().children();
    var html = $(".interpreter-editor-wrapper").html();
    var event_id = $(this).closest("tr").data().id;
    var interpreters = "<form><ul class=\"list-group\">";
    if (assigned.length) {
        assigned.each(function(i){
            var name = $(this).text();
            var interpreter_id = $(this).data().interpreter_id;
            interpreters += renderInterpreter(i,name,interpreter_id,event_id);
        });
    }
    interpreters += `</ul><input type="hidden" name="event[id]" value="${event_id}"></form>`;

    return interpreters + html;
};

/**
 * helper for rendering HTML for an interpreter
 *
 * @param  {string} index          array index to submit to server
 * @param  {string} name           interpreter name
 * @param  {string} interpreter_id
 * @param  {string} event_id
 * @return {string}
 */
const renderInterpreter = function(index,name,interpreter_id,event_id) {
    if (name.match(/confirmed$/)) {
        name = name.replace(/confirmed$/, " <span title='a confirmation email was sent' class='fa fa-check text-success'></span><span class= 'sr-only'>confirmed</span>");
    }
    return `<li class="list-group-item pr-1 py-1">
        <input name="event[interpreterEvents][${index}][interpreter]" type="hidden" value="${interpreter_id}">
        <input name="event[interpreterEvents][${index}][event]" type="hidden" value="${event_id}">
        <span class="float-left interpreter-name align-middle pt-1">${name}</span>
        <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this interpreter">
        <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove this interpreter</span></button>
    </li>`;
};

/**
 * reloads schedule data table
 * @param  {string} url for fetching data
 * @return object
 */
const reload_schedule = function(url){
    url = url || document.location.pathname;
    return $.get(url)
        .done((data)=>{
            var new_data = $(data).html();
            console.log("shit changed? "+(previous_data != new_data));
            if (previous_data != new_data) {
                previous_data = new_data;
                $("#schedule-table").html(new_data).trigger("io.reload");
            }
        });
};

var previous_data, interval;

/**
 * checks if interpreter is banned by submitter|judge
 *
 * @param string|int banned_by comma-delimited $ids
 * @param object event_data attributes from tr element
 * @return boolean true if interpreter is banned
 */
var check_banned = function(banned_by,event_data){
    var ids = banned_by.toString().split(",");
    if (event_data.category === "in" && event_data.judge_id) {
        return ! ids.includes(event_data.judge_id);
    } else if (event_data.category === "out" && event_data.submitter_id) {
        return ! ids.includes(event_data.submitter_id);
    } else {
        return false;
    }
};

/**
 * initializes event handlers for /admin/schedule
 *
 * @return {void}
 */
$(function() {
    var schedule_table = $("#schedule-table");
    // for later comparison
    previous_data =  schedule_table.html();

    var popover_opts = {
        html: true,
        placement: "left",
        sanitize : false,
        title : "update interpreters <a href=\"#\" class=\"close btn-cancel\" title=\"cancel\" data-dismiss=\"alert\">&times;</a>",
        content : renderInterpreterEditor,
    };

    /**
     * handles custom "io.reload" event.
     *
     * "io" as in Interpreters Office.
     */
    $("body").on("io.reload","#schedule-table",function(){
        console.log("running io.reload custom event handler");
        $(".edit-interpreters").on("click",(e)=>e.preventDefault())
            .popover(popover_opts);
        $(".admin_comments").on("click",(e)=>e.preventDefault()).popover({trigger : "hover focus",title:""});
        $("table [data-toggle=\"tooltip\"]").tooltip();
        if ($(".no-events").length) {
            schedule_table.removeClass("table-hover");
        } else {
            schedule_table.addClass("table-hover");
        }
    });

    // --- (delegated) event handlers for table containing schedule new_data

    schedule_table.on("click",".btn-cancel",function(event){
        event.preventDefault(); // console.log("closing the popover");
        $(this).parents(".popover").popover("hide");
    })
    
    /**
     * removes an interpreter from the popover
     */
        .on("click",".popover-body .btn-remove-item",function(event){
            event.preventDefault();
            console.log("interpreter to be deleted: "+$(this).prev().text());
            $(this).prev(".interpreter-name").css({textDecoration:"line-through"});
            $(this).closest("li").slideUp(500,function(){$(this).remove();});
        })
    /**
     * submits interpreter data
     */
        .on("click",".popover-body .btn-success",function(event){
            event.preventDefault();
            var csrf = schedule_table.data("csrf");
            var button = $(this);
            var popover_body = button.closest(".popover-body");
            var popover = popover_body.parent();
            var event_id = popover.data().event_id;
            var data = popover_body.children("form").serialize() + `&csrf=${csrf}`;
            $.post("/admin/schedule/update-interpreters/"+event_id,data)
                .then((response)=>{
                    var element = popover.data("bs.popover").element;
                    var td = $(element).parent().prev("td");
                    td.html(response.html);
                    if (response.status === "success") {
                        return popover.popover("hide");
                    // console.warn("praise the lord!!");
                    }
                    if (response.status == "error") {
                        popover_body.children("form").hide();
                        popover_body.find(".input-group")
                            .html(`<p class="alert alert-warning border border-danger">Oops! ${response.message}</p>`);
                        button.attr({disabled:true});
                        button.next("button").one("click",function(){
                            td.parent().slideUp();
                        });
                    }
                    if (response.validation_errors) {
                        if (response.validation_errors.csrf) {
                            var message = "Sorry &mdash; it appears your security token has timed out. Please refresh this page and try again";
                            popover.addClass("alert alert-warning").html(message);
                        } else {
                            fail(response);
                        }
                    }
                })
                .fail(fail);
        })
        /**
         * adds to the popover the interpreter to be assigned
         */
        .on("click",".popover-body .btn-add-interpreter",function(event){
            event.preventDefault();
            var btn = $(this);
            var option = btn.prev().children("option:selected");
            var interpreter_id = option.val();
            var list = btn.closest(".popover-body").find("ul.list-group");
            var existing = list.find(`input[value="${interpreter_id}"][name*="interpreter"]`);
            if (existing.length) { return; }
            var name = option.text();
            var event_id = btn.closest(".popover").data().event_id;
            var index;
            if (! list.children().length) {
                index = 0;
            } else {
                var last = list.find("li > input").last();
                var m = last.attr("name").match(/\[(\d+)\]/);
                if (m.length) {
                    index = parseInt(m.pop()) + 1;
                } // else { something is very wrong. }
            }
            list.append(renderInterpreter(index,name,interpreter_id,event_id));
            btn.prev("select").val("");
        })
    /**
     * when popover is shown, sets data attributes and populates
     * interpreter select element
     */
        .on("shown.bs.popover",".edit-interpreters",(e)=>{
            var language_id = $(e.target).parent().prev().data().language_id;
            var event_data = $(e.target).closest("tr").data();
            var event_id = event_data.id;
            var popover = $(".popover").last();
            popover.data({event_id});
            var interpreter_select = popover.find("select");

            $.getJSON("/admin/schedule/interpreter-options?language_id="
        + language_id + "&csrf=1" )
                .then((response)=>{
                    var options = response.options.map(function(item){
                        var opt = $("<option>").val(item.value).text(item.label);
                        if (item.attributes) {
                            for (let [key, value] of Object.entries(item.attributes)) {
                                opt.attr(key,value);
                            }
                        }
                        // disabled the option if the interpreter has issues with this
                        // event's submitter|judge
                        if (item.attributes && item.attributes["data-banned_by"]) {
                            var disabled = check_banned(item.attributes["data-banned_by"],event_data);
                            if (disabled) { opt.attr({disabled}); }
                        }
                        return opt;
                    });
                    interpreter_select.append(options);
                    schedule_table.data({csrf:response.csrf});
                });
        })
    /**
     * toggles display of additional defendant-names
     */
        .on("click", "a.expand-deftnames", function(e){
            e.preventDefault();
            $(this).hide().siblings().slideDown();
        })
        .on("click","a.collapse-deftnames", function(e){
            e.preventDefault();
            var self = $(this);
            self.hide().siblings().not(":first-of-type").hide();
            self.siblings("a.expand-deftnames").show();
        })
    /**
     * triggers click on interpreter-edit button when they click adjacent
     * TD element containing interpreter names
     */
        .on("click",".interpreters-assigned",function(){
            $(this).next("td").children("a").trigger("click");
        });

        
        // initialize Bootstrap popover for editing interpreters
    $(".edit-interpreters").on("click",(e)=>e.preventDefault()).popover(popover_opts);
    $("[data-toggle=\"tooltip\"]").tooltip();

    // refresh table when they change language filter
    $("#language-select").on("change",function(){
        // strip route parameters /yyyy/mm/dd
        var url = document.location.pathname.replace(/\/\d+/g,"");
        url += "?language=" + $(this).val();
        return reload_schedule(url);
    });
    
    $(".admin_comments").on("click",(e)=>e.preventDefault()).popover({trigger : "hover focus",title:""});
    
    //-------- end #schedule-table event handlers --------------

    /*
    decide interval for reloading the schedule data
    */
    var schedule_date = new moment($(".display-date").text(),"DD MMM YYYY");
    var now = new moment();
    var is_current = schedule_date.format("YYYYMMDD") >= now.format("YYYYMMDD");

    if (is_current) {
        interval = schedule_refresh_intervals.current * 1000;
    } else {
        interval = schedule_refresh_intervals.historical * 1000;
    }
    console.debug(`refresh interval: ${interval/1000} seconds`);

    // initialize jquery-ui datepicker
    var date_input = $("#date-input");    
    date_input.datepicker({
        //defaultDate: schedule_date.toDate(),
        changeMonth: true,
        changeYear: true,
        yearRange : "-20:+4",
        selectOtherMonths : true,
        showOtherMonths : true,
        // go to selected date when they choose from the datepicker
        onSelect : function() {
            var dateObj = date_input.datepicker("getDate");
            var date = $.datepicker.formatDate( "/yy/mm/dd", dateObj);
            var url = document.location.pathname.replace(/\/\d+/g,"");
            document.location = url + date;
        }
    });
    /* check for pending requests every 3 minutes */
    var requests_pending = $("#requests-pending span");
    if (requests_pending.length) {
        var requests_interval = 180000;
        window.t = window.setTimeout(function check_requests(){
            console.log("hello.");
            $.get("/admin/requests/count").then(res=>{
                if ("number" === typeof res.pending) {
                    requests_pending.text(res.pending);
                }       
                window.t = window.setTimeout(check_requests,requests_interval);
            }).fail(res=>{fail(res); window.t = null;});
        },requests_interval);    
    }

    /**
     * periodically reloads schedule data.
     *
     * If the data has not changed since last fetched, don't update
     * the DOM. We use an IIFE because we change shit as soon as it's
     * rendered -- otherwise it would replace the data unnecessarily. We
     * are named rather than anonymous so we can call ourself recursively.
     */
    (function run(){
        window.schedule_timer = window.setTimeout(function(){
            if ($(".popover").length !== 0) {
                console.debug("popovers are up, run() is starting over");
                return run();
            }
            $.get(document.location.pathname).done((data)=>{
                var new_data = $(data).html();
                if (previous_data != new_data) {
                    console.debug("shit changed");
                    previous_data = new_data;
                    $("#schedule-table").html(new_data).trigger("io.reload");
                }
                run();
            }).fail(()=> console.warn("shit happened!")
            );
        },interval);
    })();
});
