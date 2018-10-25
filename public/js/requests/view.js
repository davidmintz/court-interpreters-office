var $, moment;
const timestamp_format = "YYYY-MM-DD HH:mm:ss";
const seconds = 30;
const request_div = $("#request-details");
var timer;

var disable_buttons = function(){
    $(".request-update").addClass("disabled")
        .css({textDecoration:"line-through"})
        .attr({title:"this action is no longer available"})
        .on("click",(event)=>event.preventDefault());
};

$(function(){

    var str = `${$("#date").text().trim()} ${$("#time").text().trim()}`;
    var deadline = new moment(request_div.data("deadline"),timestamp_format);
    var scheduled_datetime = new moment(str,"ddd DD-MMM-YYYY h:mm a");
    //var scheduled_datetime = new moment(deadline).add(10,'s');

    var editable = scheduled_datetime.isAfter(deadline);
    console.log("on page load, can edit? "+editable);
    if (editable) {
        timer = window.setInterval(()=> {
            var str = deadline.add(seconds,"seconds").format(timestamp_format);
            request_div.data({deadline: str});
            if (! scheduled_datetime.isAfter(deadline)) {
                editable = false;
                console.warn("time has expired");
                disable_buttons();
                window.clearInterval(timer);

            }
        }, seconds * 1000);
    }
});
