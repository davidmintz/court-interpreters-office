$(function(){
    var event_id =  $('#event_id').val() || $(".event-details").data("event_id");
    if (! event_id) {
        console.warn("could not figure out event id in event-delete.js");
        return;
    }
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! confirm("Are you sure you want to delete this event from the schedule?")){
            return;
        }
        console.log("deleting shit...");
        var url = '/admin/schedule/delete/'+event_id;
        var redirect_url = document.referrer||'/admin/schedule';
        $.post(url,"json")
            .success(function(response){
                if (response.status === "success" ) {
                    document.location = redirect_url;
                } else {
                    // deal with it
                }
            });
    });
});
