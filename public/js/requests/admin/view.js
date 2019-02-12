var moment, schedule_request_callback, show_error_message;
$(function(){
    $("#btn-schedule-request").on("click",function(){
        var data = $(this).data();
        $.post(`/admin/requests/schedule/${data.request_id}`,{csrf:data.csrf})
        .then((response) =>{
            if (response.status === "success") {
                schedule_request_callback(response);
                $("#request-status").text("scheduled");
                $("#btn-schedule-request").remove();
            }
            if (response.status === "error") {
                show_error_message(response);
                if (response.message.match(/already.*schedule/i)) {
                    $(this).remove();
                }
            }
        }).fail(fail);
    });
});
