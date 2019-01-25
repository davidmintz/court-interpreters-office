var moment, schedule_request_callback;

var update_verbiage = function(count) {
    if ( "undefined" === typeof count) {
        count = $("tbody tr").length;
    }
    var verbiage = `${count} request`;
    if (count !== 1) {
        verbiage += "s";
    }
    $("#requests-pending").text(verbiage);
}

$(function(){

    $("#tab-content").on("click","a.request-add",function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        var id = row.data().id;
        $.post(`/admin/requests/schedule/${id}`)
        .then((response)=>{
            console.log(response);
            if (response.status === "success") {
                schedule_request_callback(response);
                var count = row.siblings().length;
                row.slideUp(function(){
                    $(this).remove();
                    update_verbiage(count);
                });
            }
            if (response.status === "error") {
                show_error_message(response);
                if (response.message.match(/already.*schedule/i)) {
                    row.remove();
                    update_verbiage();
                }
            }
        }).fail(fail);
    // to keep track of whatever dropdown is being shown
    }).on("show.bs.dropdown","td.dropdown",function(event){
            var id = $(event.target).parent().data().id;
            $("table").data({dropdown_displayed : id});
        }
    ).on("hide.bs.dropdown","td.dropdown",function(event){
            var id = $(event.target).parent().data().id;
            $("table").data({dropdown_displayed :null});
        }
    );
    var html = $("tbody").html();
    var refresh = function refresh(){
        $.get(document.location.href)
        .then((response)=>{
            var doc = $(response);
            var this_html = doc.find("tbody").html();
            if (! this_html) { console.warn("error? no TBODY html");  }
            var updated = html !== this_html;
            console.warn("updated? "+ (updated ? "yes" : "no"));
            if (updated) {
                html = this_html;
                $("tbody").html(this_html)
                // ...and restore any previously-showing dropdown
                var dropdown_id = $("table").data("dropdown_displayed");
                if (dropdown_id) {
                    console.log("we're a class act, restoring your dropdown");
                    // could not get .dropdown("show") to work \-:
                    $(`#request-dropdown-${dropdown_id}`).trigger("click");
                }
                update_verbiage();
            }
            setTimeout(refresh,5000);
        });
    };
    setTimeout(refresh,5000);

});
