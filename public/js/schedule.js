/**  public/js/schedule.js */

$(function() {

    var date_input = $('#date-input');
    var schedule_table = $('#schedule-table');
    /** @todo refactor for DRYness' sake. trigger custom event? */
    if ($('.no-events').length) {
        schedule_table.removeClass("table-hover");
    }

    // refresh table when they change language filter
    $('#language-select').on("change",function(event){
        // strip route parameters /yyyy/mm/dd
        var url = document.location.pathname.replace(/\/\d+/g,'');
        url += '?language=' + $(this).val();
        $.get(url, function(html){
            var table = $('#schedule-table'); // yes, another local variable
             table.replaceWith(html);
            // if there's no data in the table, remove "hover" effect
            if ($('.no-events').length) {
                table.removeClass("table-hover");
            } else {
                table.addClass("table-hover");
            }
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

    // reload every so often
    var interval = 30 * 1000;
    var timer;
    var previous = document.querySelector("#schedule-table").outerHTML.trim();
    $('[data-toggle="tooltip"]').tooltip();

    (function run(){

        timer = window.setTimeout(function(){
            $.get(document.location.href).done((data, textSatus, jqXHR)=>{

                html = jqXHR.responseText.trim();
                if (html != previous ) {
                    console.log("shit CHANGED!?");
                    $("#schedule-table").replaceWith(data);
                    previous = html;
                } else {
                    console.log("shit has not changed");
                }
                run();
            }).fail(()=> console.warn("shit happened!")
            );
        },interval);
    })()

});
