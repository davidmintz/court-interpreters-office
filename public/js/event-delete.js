$(function(){
    var event_id =  $('#event_id').val() || $(".event-details").data("event_id");
    var csrf_token = $('#csrf').val() || $('#btn-delete').data("csrf");
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
        var url = `/admin/schedule/delete/${event_id}`;
        var redirect_url = `${window.basePath}/admin/schedule/view/${event_id}`;
        $.post(url,{csrf:csrf_token},"json")
            .success(function(response){
                if (response.status === "success" ) {
                    document.location = redirect_url;
                } else {
                    /** @todo deal with it */
                }
            }).fail(fail);
    });
});
