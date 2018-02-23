/**  public/js/schedule.js */

$(function() {
    // strip route parameters /yyyy/mm/dd
    var url = document.location.pathname.replace(/\/\d+/g,'');
    var date_input = $('#date-input');
    var schedule_table = $('#schedule-table');
    // refresh table when they change language filter
    $('#language-select').on("change",function(event){
        $('#schedule-table tbody').load(
            url + '?language=' + $(this).val() + ' #schedule-table tbody tr',
            function(){
                // if there's no data in the table, remove "hover" effect
                if ($('.no-events').length) {
                    schedule_table.removeClass("table-hover");
                } else {
                    schedule_table.addClass("table-hover");
                }
            }
        );
    });
    // go to the selected date when they choose from the datepicker
    date_input.datepicker({
        changeMonth: true,
        changeYear: true,
        selectOtherMonths : true,
        showOtherMonths : true,
        onSelect : function(dateText, datepicker) {
            var dateObj = date_input.datepicker("getDate");
            var date = $.datepicker.formatDate( "/yy/mm/dd", dateObj);
            document.location = url + date;
        }
    });
    /** @todo refactor for DRYness' sake. trigger custom event */
    if ($('.no-events').length) {
        schedule_table.removeClass("table-hover");
    }

});
