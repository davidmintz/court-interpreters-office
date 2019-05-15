/*
global $, fail, should_suggest_email
*/
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
        var url = `/admin/schedule/delete/${event_id}`;
        //var redirect_url = `${window.basePath}/admin/schedule/view/${event_id}`;
        $.post(url,{csrf:csrf_token},"json")
            .success(function(response){
                if (response.status === "success" ) {
                    $(".alert-success").addClass("event-deleted");
                    var html = "This event has now been deleted from the schedule.";
                    if (should_suggest_email()) {
                        var who = 'interpreter';
                        if ($("span.interpreter").length > 1) {
                            who += "s";
                        }
                        html += ` <a id="link-email-suggest" href="#">Email notification to the ${who}?</a>`;
                        $(".container").on("click","#link-email-suggest",function(e) {
                            e.preventDefault();
                            $("#btn-email").trigger("click",{interpreter_update_notice: "cancellation"});
                        });
                    }
                    $(".alert-success p").html(html).parent().show();
                    // "edit" and "delete" buttons should be disabled
                    $(`a.btn:contains("edit"), a.btn:contains("delete")`).addClass("disabled").attr({disabled:"disabled"});
                } else {
                    /** @todo deal with it */
                }
            }).fail(fail);
    });
});
