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
    /** periodically checks request date and time against the deadline */
    var str = `${$("#date").text().trim()} ${$("#time").text().trim()}`;
    var deadline = new moment(request_div.data("deadline"),timestamp_format);
    var scheduled_datetime = new moment(str,"ddd DD-MMM-YYYY h:mm a");
    //var scheduled_datetime = new moment(deadline).add(10,'s');
    var editable = scheduled_datetime.isAfter(deadline);
    //console.log("on page load, can edit? "+editable);
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

    /**
     * shows a dialog to confirm cancellation
     */
    $("#btn-cancel").on("click",function(event){
        event.preventDefault();
        $("#confirmation-message").html(`<p>Are you sure you want to cancel this request
            for ${$("#language").text()} interpreting and delete this database record?</p>`);
        $("#modal-confirm-cancel").modal();
    });

    /**
     * deletes the request for interpreting services
     */
    $("#btn-confirm-cancellation").on("click",function(){
        var id = $("#request-details").data().id;
        var description = `${$("#language").text()} for a ${$("#event-type").text()} on `
            + `${$("#date").text()} at ${$("#time").text()}`
        $.post( `${window.basePath || ""}/requests/cancel/${id}`,{description})
            .done(function(response){
                window.document.location = `${window.basePath || ""}/requests/list`;
            })
            .fail(fail)
            .complete(()=>{$("#modal-confirm-cancel").modal("hide")});
    })
});
