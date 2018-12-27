/**  public/js/schedule.js */

const renderInterpreterEditor = function(){

    var assigned = $(this).parent().prev().children();
    //console.warn(`assigned: ${assigned.length}`);
    var html = $(".interpreter-editor-wrapper").html();
    if (assigned.length) {
        var interpreters = `<ul class="list-group">`;
        assigned.each(function(){
            var name = $(this).text();
            interpreters +=
                `<li class="list-group-item pr-1 py-1">
                    <span class="float-left interpreter-name align-middle pt-1">${name}</span>
                    <button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove this interpreter">
                    <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove this interpreter</span></button>
                </li>`;

        })
        interpreters += `</ul>`;
        html = interpreters + html;
    }
    return html;

}

$(function() {
    var popover_opts = {
        html: true,
        placement: "left",
        title : `update interpreters <a href="#" class="close btn-cancel" title="cancel" data-dismiss="alert">&times;</a>`,
        content : renderInterpreterEditor,
        container : "body"
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
    .on("click",".btn-cancel",function(){
        console.log("close the popover");
        $(this).parents(".popover").popover('hide');
    })
    .on("click",".popover-body .btn-remove-item",function(){
        /** to be continued */
        console.log("interpreter to be deleted: "+$(this).prev().text());
    })
    .on("click",".popover-body .btn-add-interpreter",function(){
        /** to be continued */
        console.log("interpreter to be added: "+$(this).prev().children("option:selected").text());
    });;

    $('[data-toggle="tooltip"]').tooltip();
    $(".edit-interpreters").popover(popover_opts);

    var date_input = $('#date-input');
    var schedule_table = $('#schedule-table');

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
    var interval;
    if (is_current) {
        interval = 20 * 1000;
    } else {
        interval = 180 * 1000;
    }
    console.log(`refresh interval set to ${interval/1000} seconds`);

    // for later comparison
    var previous =  schedule_table.html();
    // reload periodically. if the data has not changed since last fetched, don't
    // update the DOM.
    (function run(){
        window.timer = window.setTimeout(function(){
            $.get(document.location.href).done((data)=>{
                var new_data = $(data).html();
                //var changed = previous != new_data;
                //console.log("changed? "+changed);
                if (previous != new_data) {
                    previous = new_data;
                    $("#schedule-table").html(new_data).trigger("io.reload");
                }
                run();
            }).fail(()=> console.warn("shit happened!")
            );
        },interval);
    })();
});

const stop = function(){ window.clearTimeout(timer)};
