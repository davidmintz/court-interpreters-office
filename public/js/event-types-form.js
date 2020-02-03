/**  public/js/event-types-form.js */

/* global  $,  fail */

$(function(){
    $("#btn-delete").on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this event-type?")) {
            return;
        }
        var data = $("form#event-type").data();
        var name = data.event_type_name;
        var id = $("input[name=\"id\"]").val();
        var url = `/admin/event-types/delete/${id}`;
        $.post(url,{name})
            .done(()=>
                window.document.location = `${window.basePath||""}/admin/event-types`)
            .fail(fail);
    });
});
