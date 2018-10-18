var $, moment;
const timestamp_format = "YYYY-MM-DD HH:mm:ss";
const seconds = 30;
const request_div = $("#request-details");

$(function(){

    var str = `${$("#date").text().trim()} ${$("#time").text().trim()}`;
    var scheduled_datetime = new moment(str,"ddd DD-MMM-YYYY h:mm a");
    var deadline = new moment(request_div.data("deadline"),timestamp_format);
    var editable = scheduled_datetime.isAfter(deadline);
    console.warn("can edit? "+editable);
    if (editable) {
        window.setInterval(()=> {
            var str = deadline.add(seconds,"seconds").format(timestamp_format);
            request_div.data({deadline: str});
            console.warn("editable? "+editable);
        }, seconds * 1000);
    }
});
