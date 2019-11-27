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
               $(".start_date").data({start_date:res.start_date});
           }).fail(fail);
        }
    });

    /**
     * initialize dialog for overriding currently assigned person
     */
    $("#dialog").data({rotation_start_date: $(".start_date").data("start_date")})
    .on("show.bs.modal",(e)=> {
        var pretty_date = $(".current-assignment .assignment-date").text().replace(":","");
        var date = new moment(pretty_date, "ddd DD-MMM-YYYY").format("YYYY-MM-DD");
        var task_id = $(".task").data("task_id");
        $.get(`/admin/rotations/assignments/${date}/${task_id}`)
        .then(
            res => {
                console.warn(`${res.start_date} is start date of the newly-fetched rotation`);
                if (res.start_date !== $("#dialog").data("rotation_start_date")) {
                    console.warn("which differs from that currently displayed");
                    var n = $("#dialog .form-check").length - 1;
                    slice = $("#dialog .person-wrapper").slice(0,n);
                    let html = "";
                    res.rotation.forEach((p, i)=>{
                        html += `<div class="person-wrapper border border-bottom-0 px-2 py-1">
                            <div class="form-check">
                                <input data-id="${p.id}" class="form-check-input person" type="radio" name="person" id="person-${p.id}" value="person">
                                <label class="form-check-label" for="person-${p.id}">
                                    ${p.name}
                                </label>
                            </div>
                        </div>`;
                    })
                    $("#dialog p.subtitle").after(html);
                    slice.remove();
                    $("#dialog").data({rotation_start_date:res.start_date});
                } else {
                    console.log("start dates match");
                }
                var current = $(".assignment-person");
                var disabled = $(".person:disabled");
                if (current.data("id") !== disabled.data("id")) {
                    // need to enable/disabled yadda
                    disabled.removeAttr("disabled");
                    $(`#dialog .person[data-id=${res.assigned.id}]`).attr({disabled:true});
                }
            }
        );
        $("#dialog .modal-title .assignment-date").text(`, ${pretty_date}`);

    })
    .on("click",`option[value="other"]`,
        (e)=>{
            console.log("shit was clicked, display input for autocompletion etc");
            $("#dialog select").attr({hidden: true});
            $("#dialog input").removeAttr("hidden");
        }
    );


});
