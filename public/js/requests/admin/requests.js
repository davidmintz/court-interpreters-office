var moment;

var schedule_request_callback = function(response) {

    var date = moment(response.event_date,"YYYY-MM-DD");
    var schedule_url = `/admin/schedule/${date.format("YYYY/MM/DD")}`;
    var schedule_text = "schedule for "+date.format("ddd DD-MMM-YYYY");
    var event_url = `/admin/schedule/view/${response.event_id}`;
    $("#message-success p").html
        (`Successfully added this request as a <a href="${event_url}">new event</a>
        on the <a href="${schedule_url}">${schedule_text}</a>.`)
    .parent().show();
};

var show_request_error_message = function(response) {
    var div = $("#message-error");
    if (0 === div.length) {
        div = $("<div/>").attr({id:"message-error"})
            .addClass("alert alert-warning alert-dismissible").append(
                `<p class="mb-0"></p>
                <button type="button" class="close" data-dismiss="alert" aria-label="close">
                    <span aria-hidden="true">&times;</span>
                </button>`
            );
        $("h2").first().after(div);
    }
    $("#message-success").hide();
    div.children("p").html(response.message);
    div.show();
};
