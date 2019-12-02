/**  public/js/admin/tasks.js */

/*
global $, fail, moment
*/

$(function(){
    var autocomplete_field = $("#rotation-autocomplete");
    autocomplete_field.autocomplete({
        source: "/admin/people/autocomplete",
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            $("#person-autocomplete").val(ui.item.value);
            $(this).val(ui.item.label);
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        }
    });
    $("#calendar").datepicker({
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        dateFormat : "yy-mm-dd",
        onSelect : function(date, instance){
           var task_id = $(".task").data("task_id");
           var refresh_rotation = false;
           if (date < $(".start_date").data("start_date")) {
               console.log("selected date predates start of currently displayed rotation");
               refresh_rotation = true;
           }
           $.get(`/admin/rotations/assignments/${date}/${task_id}`)
           .then((res)=>{
               var formatted = new moment(res.date,"YYYY-MM-DD").format("ddd DD-MMM-YYYY");
               $(".task .assignment-date").text(`${formatted}: `).data({date:res.date});
               var html = "";
               var $default  = res["default"];
               if (res.assigned.id !== $default.id) {
                   html += `<span style="text-decoration:line-through">${$default.name}</span> `;
               }
               html += `${res.assigned.name}`;
               $(".assignment-person").html(html).data({id:res.assigned.id});
               if (refresh_rotation) {
                   $(".rotation").html(res.rotation.map(e=>e.name).join("<br>"));
               }
               var start_date = new moment(res.start_date,"YYYY-MM-DD");
               $(".start_date").text(start_date.format("ddd DD-MMM-YYYY"))
               $(".start_date").data({start_date:res.start_date});
           }).fail(fail);
        }
    });

    /**
     * initialize dialog for overriding currently assigned person
     */
    $("#dialog")
    .data({rotation_start_date: $(".start_date").data("start_date")})
    .on("show.bs.modal",(e)=> {
        var pretty_date = $(".current-assignment .assignment-date").text().replace(":","");
        var date = new moment(pretty_date, "ddd DD-MMM-YYYY").format("YYYY-MM-DD");
        var task_id = $(".task").data("task_id");
        $.get(`/admin/rotations/assignments/${date}/${task_id}`)
        .then(res =>
            {
                console.warn(`${res.start_date} is start date of the newly-fetched rotation`);
                if (res.start_date !== $("#dialog").data("rotation_start_date")) {
                    console.warn("which differs from that currently displayed");
                    var n = $("#dialog .form-check").length - 1;
                    slice = $("#dialog .person-wrapper").slice(0,n);
                    // yes, ugly. requires us to maintain HTML in both here
                    // and in the viewscript
                    let html = "";
                    res.rotation.forEach((p, i)=>{
                        html +=
                         `<div class="person-wrapper border border-bottom-0 px-2 py-1">
                            <div class="form-check">
                                <input data-id="${p.id}" class="form-check-input person" value="${p.id}" type="radio" name="person" id="person-${p.id}" value="person">
                                <label class="form-check-label" for="person-${p.id}">
                                    ${p.name}
                                </label>
                            </div>
                         </div>`;
                    })
                    $("#dialog p.subtitle").after(html);
                    slice.remove();
                    $("#dialog").data({rotation_start_date:res.start_date});
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
    .on("hidden.bs.modal",()=>{
        var error_div = $(".modal-footer .alert");
        if (error_div.text()) {
            error_div.attr({hidden:true});
        }
    });


    var radio = $("#dialog input[type=radio]").last();
    var autocomplete_field = $("#rotation-autocomplete");
    autocomplete_field.on("focus",e=>{
        if (! radio.attr("checked")) { radio.attr({checked:true});}
    })
    radio.on("click",()=>autocomplete_field.focus());

    $("#dialog .modal-footer > .btn-primary").on("click",(e)=>{
        console.warn("rock and ROLL!");
        var person = $("#dialog input:checked").val();
        if (! person) {
            $("#dialog .modal-footer .alert").text(
                "Please select a person, or else cancel."
            ).removeAttr("hidden");
            return;
        }
        var duration = $(".task").data('task_frequency') === 'WEEK' ?
            $("#duration input:checked").val() : 'DAY';

        // pull together data
        data = {
            date:  $(".assignment-date").data("date"),
            task : $(".task").data("task_id"),
            person, duration
        };

        console.warn(data);
    });
});
