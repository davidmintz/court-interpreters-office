/**  public/js/locations-form.js */

$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this location?")) {
            return;
        }
        var data = $('form#location').data();
        var name = data.location_name
        var id = $('input[name="id"]').val();
        var url = `/admin/locations/delete/${id}`;
        $.post(url,{name})
        .done(()=>
                window.document.location = `${window.basePath||""}/admin/locations`)
        .fail(fail);
    });
});
