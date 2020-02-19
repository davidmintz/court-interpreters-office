/**
 * event handlers for the Task creation and the Task-Rotation creation
 * forms
 */

/*
global $, fail, displayValidationErrors
*/
$(function(){

    var autocomplete_field = $("#autocomplete-members");
    autocomplete_field.autocomplete({
        source: (request,response) => {
            var params = { term : request.term, active : 1 };
            $.get("/admin/people/autocomplete",params).then(
                (data,statusText,jqXHR) => response(data)
            );
        },
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            if ($(`#members input[value="${ui.item.value}"]`).length) {
                //alert("You already have this person in the rotation");
                $(this).val("");
                return;
            }
            $("#members").append(
                `<li class="list-group-item pr-1 py-1"><span class="float-left person-name align-middle pt-1">${ui.item.label}</span>
                <input name="rotation[members][]" value="${ui.item.value}" type="hidden"><button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove from rotation">
                <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></li>`
            );
            $(this).val("");
            if ($("#error_members").is(":visible")) {
                $("#error_members").hide();
            }
            if ($("#error_countable").is(":visible")) {
                $("#error_countable").hide();
            }
            $("#member-sort-help").attr({hidden:()=>$("#members li").length < 2});
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        }
    })// prevent unwanted, mysterious click event from firing on .btn-remove
    .on("keypress",(e)=>{ if (e.which === 13) { e.preventDefault();} });

    autocomplete_field.autocomplete("instance")._renderItem =
     function(ul, item) {
        return $( "<li>" )
            .append($("<div>").html( `${item.label} <span class="text-muted">${item.hat}</span>` ) )
            .appendTo(ul);
     };
     
    $("#datepicker_start_date").datepicker({
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        selectOtherMonths: true,
        minDate: 0,
        constrainInput : true,
        altField: "#start_date",
        altFormat : "yy-mm-dd",
        dateFormat : "DD dd-M-yy",
        onSelect : function(date, instance){
            if ($("#error_start_date:visible").length) {
                $("#error_start_date").hide();
            }
        }
    }).on("change",function(){
        if (!$(this).val()) {$("#start_date").val("");}
    });
    $("#members").on("click","button.btn-remove-item",function(e){
        e.preventDefault();
        var div = $(this).parent();
        div.slideUp(()=>{
            div.remove();
            $("#member-sort-help").attr({hidden:()=>$("#members li").length < 2});
        });
    })
    .sortable();

    $("#btn-save").on("click",function(e){
        e.preventDefault();
        var form = $("form.task-rotation");
        if (form.attr("id")==="task-form") {
            return submit_task_form(form);
        }
        // if it looks like it will validate, display a confirmation
        if (looks_valid(form)) {
            render_form_confirmation(form);
            $("#dialog").modal({});
            return;
        } else {
            // let it get submitted (and fail validation)
            submit_task_rotation_form(form);
        }

    });

    $("#btn-confirm").on("click",function(e){
        e.preventDefault();
        var form = $("form.task-rotation");
        if (form.attr("id")==="rotation-form") {
            submit_task_rotation_form(form);
        } else {
            submit_task_form(form,true);
        }
        $("dialog").modal("hide");
    });

    /* for Task form */
    var dow = $("#day_of_week");
    var duration = $("#duration")
    duration.on("change",()=>{
        var disabled = duration.val() === "WEEK";
        if (disabled) {dow.val("");}
        dow.attr({disabled});
    }).trigger("change");
});
/** half-assed quasi-validation. If it looks like it will validate, we
will display a confirmation. If not, we just let them submit and
let the server-side validation handle it.
*/
var looks_valid = function(form) {
    if (form.attr("id") === "task-form") {
        var ok = $("#start_date").val().trim() != ""
            &&  $("#members li").length > 1
            && $("#name").val()
            && $("#frequency").val()
            && $("#duration").val();
        if (! ok) { return false; }
        if ($("#duration").val() !== "WEEK" && !$("#day_of_week").val()) {
            return false;
        }
        return true;
    } else {
        return  $("#task").val().trim() != ""
            && $("#start_date").val().trim() != ""
            && $("#members li").length > 1;
    }
}

var dummy = function(){
    $("#members").append(
        `<li class="list-group-item pr-1 py-1"><span class="float-left person-name align-middle pt-1">Anderson, Peter</span>
        <input name="rotation[members][]" value="548" type="hidden"><button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove from rotation">
        <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></li><li class="list-group-item pr-1 py-1"><span class="float-left person-name align-middle pt-1">Garcia, Humberto</span>
        <input name="rotation[members][]" value="862" type="hidden"><button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove from rotation">
        <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></li><li class="list-group-item pr-1 py-1"><span class="float-left person-name align-middle pt-1">de los Ríos, Erika</span>
        <input name="rotation[members][]" value="881" type="hidden"><button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove from rotation">
        <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></li>`);
};

const render_form_confirmation = function(form) {
    var task = $("#task option:selected").text() || $("#name").val();
    var date = $("#datepicker_start_date").val();
    var list = "<ol>";
    $(".person-name").each(function(){
        list += `<li>${$(this).text()}</li>`;
    });
    list += "</ol>";
    if (form.attr("id")=== "rotation-form") {
        var html = `<p>You are about to set the following rotation for
        <strong>${task}</strong> effective as of <strong>${date}</strong>:</p>
        ${list} <p>Continue?</p>`
    } else {
        var description = $("#description").val().trim() ?
            `<strong>${$("#description").val().trim()}</strong>`:"<em>(none)</em>";
        html = "<p>You are about to create the following rotating task</p>";
        html += `Name: <strong>${task}</strong><br>`;
        html += `Description: ${description}<br>`;
        html += `Rotation starts: <strong>${date}</strong><br>`;
        html += `Rotation members: ${list} <p>Continue?</p>`;
    }
    $("#dialog .modal-body").html(html);
};

const submit_task_form = function(form,confirmed){
    if (confirmed) {
        $("#dialog").modal("hide");
    }
    if (looks_valid(form) && !confirmed) {
        console.log("looks valid");
        render_form_confirmation(form);
        $("#dialog").modal({});
    } else {
        console.log("looks NOT valid, or it is confirmed");
        var url = form.attr("action");
        console.log(`submitting to ${url}`);
        $.post(url,form.serialize()).then(res=>{
            if (res.validation_errors) {
                return displayValidationErrors(res.validation_errors);
            } // else...
            document.location = `${window.basePath}/admin/rotations`;
        }).fail((res)=>{ fail(res); });
    }
};

const submit_task_rotation_form = function(form){
    var url = form.attr("action");
    $.post(url,form.serialize())
    .then(res=>{
        if (res.validation_errors) {
            return displayValidationErrors(res.validation_errors);
        }
        var redirect = `${window.basePath}/admin/rotations/view/${$("#task").val()}`;
        document.location = redirect;
    }
    ).fail((res) => {$("#dialog").modal("hide"); fail(res);});
};
