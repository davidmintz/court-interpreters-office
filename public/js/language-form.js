/**  public/js/language-form.js */
$(function(){
    $('#btn-delete').on("click",function(event){
        event.preventDefault();
        if (! window.confirm("Are you sure you want to delete this language?")) {
            return;
        }
        var name = $('form#language').data('language_name');
        var url = $('form#language').data('redirect_url');
        var id = $('input[name="id"]').val();
        $.post('/admin/languages/delete/'+id,{name:name},function(response){
            document.location = url;
        });
    });
});
