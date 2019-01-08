/**  public/js/schedule.js */

const renderInterpreterEditor = function(){

    var assigned = $(this).parent().prev().children();
    var html = $(".interpreter-editor-wrapper").html();
    var event_id = $(this).closest("tr").data().id;
    var interpreters = `<form><ul class="list-group">`;
    if (assigned.length) {
        assigned.each(function(i){
            var name = $(this).text();
            var interpreter_id = $(this).data().interpreter_id;
            interpreters += renderInterpreter(i,name,interpreter_id,event_id);
        })
    }
    interpreters += `</ul><input type="hidden" name="event[id]" value="${event_id}"></form>`;

    return interpreters + html;
};

const renderInterpreter = function(index,name,interpreter_id,event_id) {
    return `<li class="list-group-item pr-1 py-1">
        <input name="event[interpreterEvents][${index}][interpreter]" type="hidden" value="${interpreter_id}">
        <input name="event[interpreterEvents][${index}][event]" type="hidden" value="${event_id}">
        <span class="float-left interpreter-name align-middle pt-1">${name}</span>
        <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this interpreter">
        <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove this interpreter</span></button>
    </li>`;
}

const reload_schedule = function(){
    return $.get(document.location.href)
    .done((data)=>{
        var new_data = $(data).html();
        console.log("has shit changed? "+(previous_data != new_data));
        if (previous_data != new_data) {
            previous_data = new_data;
            $("#schedule-table").html(new_data).trigger("io.reload");
        }
    });
};

const timer_start = function(){
    console.debug(`timer_start(): timer id was: ${window.schedule_timer}`);
    if (window.schedule_timer) {
        console.debug("timer already exists. returning");
        return;
    }
    window.schedule_timer = window.setTimeout(
        reload_schedule, interval
    );
    console.debug(`timer_resume() set timer: ${window.schedule_timer}`);
};
const timer_stop = function(){
    console.debug("timer_stop() clearing timer id: "+window.schedule_timer);
    window.clearTimeout(window.schedule_timer)
    window.schedule_timer = null;
};

var previous_data, interval;

$(function() {
    var schedule_table = $('#schedule-table');
    // for later comparison
    previous_data =  schedule_table.html();

    var popover_opts = {
        html: true,
        placement: "left",
        title : `update interpreters <a href="#" class="close btn-cancel" title="cancel" data-dismiss="alert">&times;</a>`,
        content : renderInterpreterEditor//,
        // maybe try to set this dynamically to the td's parent tr?
        //container : "body"
    };

    $("body").on("io.reload",'#schedule-table',function(event){
        console.log("io.reload custom event handler");
        $('table [data-toggle="tooltip"]').tooltip();
        $(".edit-interpreters").popover(popover_opts);
        if ($('.no-events').length) {
            schedule_table.removeClass("table-hover");
        } else {
            schedule_table.addClass("table-hover");
        }
    })
    .on("click",".btn-cancel",function(event){
        event.preventDefault();
        console.log("closing the popover");
        $(this).parents(".popover").popover("hide");
    })
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
        var popover_body = $(this).closest(".popover-body");
        var popover = popover_body.parent();
        var event_id = popover.data().event_id;
        var data = popover_body.children("form").serialize() + `&csrf=${csrf}`;
        $.post("/admin/schedule/update-interpreters/"+event_id,data)
        .success((response)=>{
            if (response.status === "success") {
                var element = popover.data("bs.popover").element;
                var td = $(element).parent().prev("td");
                td.html(response.html);
                popover.popover("hide");
                return console.warn("praise the lord!!");

            }
            if (response.validation_errors) {
                if (response.validation_errors.csrf) {
                    var message = `Sorry &mdash; it appears your security token has timed out. Please refresh this page and try again`;
                    popover.addClass("alert alert-warning").html(message);
                } else {
                    fail(response);
                }
            }
        })
        .fail(fail);
        //.done(()=>start_timer());
    })
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
    // when the popover is shown, set some data attributes and populate
    // the interpreter dropdown. TO DO: cache interpreter-dropdown data
    .on("shown.bs.popover",".edit-interpreters",(e)=>{
        var language_id = $(e.target).parent().prev().data().language_id;
        var event_id = $(e.target).closest("tr").data().id;
        var popover = $(".popover").last();
        popover.data({event_id});
        var interpreter_select = popover.find("select");
        $.getJSON("/admin/schedule/interpreter-options?language_id="
            + language_id + "&csrf=1" )
        .success((response)=>{
            var options = response.options.map(function(item){
                return $("<option>").val(item.value).text(item.label);
            });
            interpreter_select.append(options);
            //popover.append($("<input/>").attr({type:"hidden",value:response.csrf,name:"csrf"}));
            schedule_table.data({csrf:response.csrf});
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
    $(".edit-interpreters").on("click",(e)=>{
        e.preventDefault();
        //try to make the parent row the container?
        // console.log("trying to set shit...?");
        // var element = $(e.target);
        // var container = element.closest("tr").get(0);
        // element.data({container});
    }
    ).popover(popover_opts);

    var date_input = $('#date-input');


    /* expand/collapse lists of deft names */
    schedule_table.on("click", "a.expand-deftnames", function(e){
        e.preventDefault();
        $(this).hide().siblings().slideDown();
    })
    .on("click",".interpreters-assigned",function(){
        $(this).next("td").children("a").trigger("click");
    })
    .on("click","a.collapse-deftnames", function(e){
        e.preventDefault();
        var self = $(this);
        self.hide().siblings().not(":first-of-type").hide();
        self.siblings("a.expand-deftnames").show();
    });

    // refresh table when they change language filter
    $('#language-select').on("change",function(event){
        // strip route parameters /yyyy/mm/dd
        var url = document.location.pathname.replace(/\/\d+/g,'');
        url += '?language=' + $(this).val();
        $.get(url, function(html){
            var table = $('#schedule-table'); // yes, another local variable
            table.replaceWith(html).trigger("io.reload");
        });
    });

    // initialize jquery-ui datepicker
    date_input.datepicker({
        changeMonth: true,
        changeYear: true,
        selectOtherMonths : true,
        showOtherMonths : true,
        // go to selected date when they choose from the datepicker
        onSelect : function(dateText, datepicker) {
            var dateObj = date_input.datepicker("getDate");
            var date = $.datepicker.formatDate( "/yy/mm/dd", dateObj);
            var url = document.location.pathname.replace(/\/\d+/g,'');
            document.location = url + date;
        }
    });

    // reload every 20 seconds if we're looking at today or later,
    // else once per 3 minutes
    var schedule_date = new moment($(".display-date").text(),"DD MMM YYYY");
    var now = new moment();
    var is_current = schedule_date.format("YYYYMMDD") >= now.format("YYYYMMDD");

    if (is_current) {
        interval = 20 * 1000;
    } else {
        interval = 180 * 1000;
    }
    console.log(`refresh interval set to ${interval/1000} seconds`);

    // reload periodically. if the data has not changed since last fetched, don't
    // update the DOM. We use an IIFE because we change shit as soon as it's
    // rendered, so otherwise it would replace the data unnecessarily
    (function run(){
        console.log("starting timer");
        window.schedule_timer = window.setTimeout(function(){
            $.get(document.location.href).done((data)=>{
                var new_data = $(data).html();
                console.log("has shit changed? "+(previous_data != new_data));
                if (previous_data != new_data) {
                    previous_data = new_data;
                    $("#schedule-table").html(new_data).trigger("io.reload");
                }
                run();
            }).fail(()=> console.warn("shit happened!")
            );
        },interval);
    })();

});
