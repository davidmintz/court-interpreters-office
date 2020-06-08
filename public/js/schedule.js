/* global $, moment,fail */
/* eslint-disable no-console */

// const moment = require("moment");
const reload_schedule = function(url) {
    $.get(url).then(function(res){
        $("#schedule").html(res);
    });
};
$(function(){
    // initialize jquery-ui datepicker
    var date_input = $("#date-input");
    var schedule_date = date_input.data("date");    
    date_input.datepicker({
        defaultDate: moment(schedule_date,"YYYY-MM-DD").toDate(),
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
    
    var select = $("#language-select");
    select.on("change",function(){        
        // strip any language parameter
        var url = document.location.pathname.replace(/\/[a-z]+$/i,"");
        reload_schedule(`${url}/${select.val()}`);
    });

    /**
     * toggles display of additional defendant-names
     */
    $("#schedule").on("click","a.expand-deftnames",function(e){
        e.preventDefault();
        $(this).hide().siblings().slideDown();
    }).on("click","a.collapse-deftnames", function(e){
        e.preventDefault();
        var self = $(this);
        self.hide().siblings().not(":first-of-type").hide();
        self.siblings("a.expand-deftnames").show();
    });
    
});