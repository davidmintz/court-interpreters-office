/*
global $, fail, should_suggest_email
*/
$(function(){
    var event_id =  $('#event_id').val() || $(".event-details").data("event_id");
    var csrf = $('#csrf').val() || $('#btn-delete').data("csrf");

    if (! event_id) {
        console.warn("could not figure out event id in event-delete.js");
        return;
    }
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        var context = document.location.pathname.indexOf("admin/schedule/edit") > -1 ?
            "edit" : "view";
        console.warn("this is "+context);
        $("#btn-confirm-delete").on("click",function(){
            var url = `/admin/schedule/delete/${event_id}`;
            var email_notification =  ($("#email-notification").length
                && $("#email-notification").is(":checked")) ? 1 : 0;
            console.log("email notification: "  + email_notification);
            $.post(url,{csrf,email_notification},"json")
            .then(function(response){
                if (response.status === "success" ) {
                    $("#modal-confirm-delete").modal("hide");
                    if (context === "view") {
                        // we are in admin/schedule/view/<id>
                        $(".alert-success").addClass("event-deleted");
                        /* this is getting ugly, but if we are already showing
                        them a prompt to send email, we don't need to put another
                        prompt on the screen. on the contrary... */
                        // if (email_notification && $("div.email-prompt").length) {
                        $("div.email-prompt").hide();
                        // }
                        var html = "This event has now been deleted from the schedule.";
                        if (! email_notification && should_suggest_email()) {
                            var who = "interpreter";
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
                        $(`a.btn:contains("edit"), a.btn:contains("delete")`)
                            .addClass("disabled").attr({disabled:"disabled"});
                    } else {
                        // it's the edit form
                        $("#event-form").find("textarea, .btn, option, select, input, .list-group li")
                            .addClass("disabled").attr({disabled:"disabled"});
                            $(".list-group li").css({backgroundColor: "#e9ecef"});
                        var message = "This event has now been deleted from the schedule";
                        if (email_notification) {
                            message += ", and we emailed notification to the interpreter";
                            if ($(".interpreter-assigned").length > 1) {
                                message += 's';
                            }
                        }
                        message += ".";
                        var html = `<div class="alert alert-success border border-warning shadow-sm">${message}</div>`;
                        $("#event-form").prepend(html);
                    }


                } else {
                    /** @todo deal with different responses */
                    console.warn(response);
                }
            }).fail((response)=>
            {
                $("#modal-confirm-delete").modal("hide");
                fail(response);
            });

        });
    });
});
