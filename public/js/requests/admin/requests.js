var moment;

var schedule_request_callback = function(response) {

    var date = moment(response.event_date,"YYYY-MM-DD");
    var schedule_url = `/admin/schedule/${date.format("YYYY/MM/DD")}`;
    var schedule_text = "schedule for "+date.format("ddd DD-MMM-YYYY");
    var event_url = `/admin/schedule/view/${response.event_id}`;
    $("#message-success").html
        (`Successfully added <a href="${event_url}">this request</a>
        to the <a href="${schedule_url}">${schedule_text}</a>.
        `)
    .show();

};
