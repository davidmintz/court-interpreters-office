var moment, schedule_request_callback, show_request_error_message;
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
                show_request_error_message(response);
                if (response.message.match(/already.*schedule/i)) {
                    $(this).remove();
                }
            }
        }).fail(fail);
    });
    /**
     * event handlers for email-dialog buttons
     */
    $("#email-dropdown .dropdown-item button").on("click",function(e){
        e.preventDefault();
        console.log("click shit");
    });
    $("#subject-dropdown .dropdown-item a").on("click",function(e){
        e.preventDefault();
        console.log("click other shit");
    });
    $("#btn-cancel").on("click",function(){
        $("#email-dialog").modal("hide");
    });
    // prepopulate the search thingy with the docket number
    var docket = $("div.docket").text().trim();
    if (docket) {
        $(`li.nav-item input[name="docket"]`).val(docket).trigger("change");
    }
});
