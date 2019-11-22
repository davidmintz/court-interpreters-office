/**  public/js/admin/tasks.js */

/*
global $, fail, moment
*/

$(function(){
    $("#calendar").datepicker({
        dateFormat : "yy-mm-dd",
        onSelect : function(date, instance){
           var task_id = $(".task").data("task_id");
           $.get(`/admin/rotations/assignments/${date}/${task_id}`)
           .then((res)=>{
               var formatted = new moment(res.date,"YYYY-MM-DD").format("ddd DD-MMM-YYYY");
               $(".assignment-date").text(`${formatted}: `);
               var html = "";
               var $default  = res["default"];
               if (res.assigned !== $default) {
                   html += `<span style="text-decoration:line-through">${$default}</span> `;
               }
               html += `${res.assigned}`;
               $(".assignment-person").html(html);
           }).fail(
               (res)=>{console.log("shit happened?"); fail(res)}
           );
        }
    });
    // $(".current-assignment button").on("click",(event)=>{
    //     console.log("boink");
    // });
    $("#dialog").on("show.bs.modal",(e)=> {
        var date = $(".assignment-date").text().replace(":","");
        $("#dialog .modal-title").append(date);
    })
});
