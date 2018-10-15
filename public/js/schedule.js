/**  public/js/schedule.js */



$(function() {
    $("body").on("io.reload",'#schedule-table',function(event){
        console.log("io.reload custom event hander");
        $('table [data-toggle="tooltip"]').tooltip();
        if ($('.no-events').length) {
            schedule_table.removeClass("table-hover");
        } else {
            schedule_table.addClass("table-hover");
        }
    });
    var date_input = $('#date-input');
    var schedule_table = $('#schedule-table');

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

    // go to selected date when they choose from the datepicker
    date_input.datepicker({
        changeMonth: true,
        changeYear: true,
        selectOtherMonths : true,
        showOtherMonths : true,
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
    $('[data-toggle="tooltip"]').tooltip();

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
    })()

});

var stop = function(){ window.clearTimeout(timer)};
