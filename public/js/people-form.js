var $;

$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this person?")) {
            return;
        }
        var name = $("#lastname").val();
        if ($("#firstname").val()) {
            name += ", "+ $("#firstname").val();
        }
        var id = $('input[name="person[id]"]').val();
        var url = `/admin/people/delete/${id}`;
        $.post(url,{name})
        .done(()=>
            window.document.location = `${window.basePath||""}/admin/people`)
        .fail(fail);
    });
});
