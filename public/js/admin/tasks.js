/**  public/js/admin/tasks.js */

/*
global $, fail, moment
*/

$(function(){

    $("#calendar").datepicker({
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        dateFormat : "yy-mm-dd",
        onSelect : function(date, instance){
           var task_id = $(".task").data("task_id");
           $.get(`/admin/rotations/assignments/${date}/${task_id}`)
           .then((res)=>{
               var formatted = new moment(res.date,"YYYY-MM-DD").format("ddd DD-MMM-YYYY");
               $(".task .assignment-date").text(`${formatted}: `);
               var html = "";
               var $default  = res["default"];
               if (res.assigned.id !== $default.id) {
                   html += `<span style="text-decoration:line-through">${$default.name}</span> `;
               }
               html += `${res.assigned.name}`;
               $(".assignment-person").html(html).data({id:res.assigned.id});
               $(".rotation").html(res.rotation.map(e=>e.name).join("<br>"));
               var start_date = new moment(res.start_date,"YYYY-MM-DD");
               $(".start_date").text(start_date.format("ddd DD-MMM-YYYY"))
           }).fail(fail);
        }
    });

    /**
     * initialize dialog for overriding currently assigned person
     */
    $("#dialog").on("show.bs.modal",(e)=> {
        var date = $(".current-assignment .assignment-date").text().replace(":","");
        var formatted_date = new moment(date, "ddd DD-MMM-YYYY").format("YYYY-MM-DD");
        var task_id = $(".task").data("task_id");
        $.get(`/admin/rotations/assignments/${formatted_date}/${task_id}`)
        .then(
            res => {
                $(`#rotation-people option:not([value="other"])`).remove();
                var opts = res.rotation.map(p => `<option value="${p.id}">${p.name}</option>`);
                opts.unshift(`<option value="" selected></option>`);
                $("#rotation-people").prepend(opts.join(""));
                var current = $(".assignment-person").data("id");
                $("#rotation-people").children(`[value="${current}"]`).attr({disabled:true});
            }
        );
        console.warn(date);
        $("#dialog .modal-title .assignment-date").text(`, ${date}`);

    })
    .on("click",`option[value="other"]`,
        (e)=>{
            console.log("shit was clicked, display input for autocompletion etc");
            //$("#dialog select").attr({hidden: true}); etc
        }
    );


});
