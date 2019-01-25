var moment, schedule_request_callback;
$(function(){

    $("#tab-content").on("click","a.request-add",function(){
        var row = $(this).closest("tr");
        var id = row.data().id;
        $.post(`/admin/requests/schedule/${id}`)
        .then((response)=>{
            console.log(response);
            if (response.status == "success") {
                schedule_request_callback(response);
                var count = row.siblings().length;
                row.slideUp(function(){
                    $(this).remove();
                });
                var verbiage = `${count} request`;
                if (count !== 1) {
                    verbiage += "s";
                }
                $("#requests-pending").text(verbiage);
            }
        }).fail(fail);
    });
});
