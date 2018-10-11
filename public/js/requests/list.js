/** public/js/requests/list.js */
var $, moment;
const timestamp_format = "YYYY-MM-DD HH:mm:ss";
const seconds = 30;

var init_rows = function(){
    // on page load, deadline is set to 2 business days from now
    var deadline = new moment($("#requests-table").data("deadline"),timestamp_format);
    var rows = $("#requests-table tbody tr");
    rows.each(function(){
        var td = $(this).children("td").slice(0,2).get();
        var str = td.map(x => x.textContent.trim()).join(" ");
        var scheduled_datetime = new moment(str, "DD-MMM-Y h:mm a");
        var editable = scheduled_datetime.isAfter(deadline);
        $(this).data({editable});
    });
}

$(function(){
    init_rows();
    window.setInterval(()=> {
            var table = $("#requests-table");
            var deadline = new moment(table.data("deadline"),timestamp_format);
            var str = deadline.add(seconds,"seconds").format(timestamp_format);
            table.data({deadline: str});
            init_rows();
        }, seconds * 1000);

    $("#content").on("click", ".pagination a",function(e){
        e.preventDefault();
        var page = $(this).text().trim();
        $.get(document.location.href+`?page=${page}`)
            .done(function(html){
                $("#content").html(html);
                init_rows();
            });
    });

});
