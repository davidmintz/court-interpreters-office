$(function(){
    var name_element = $("#name");
    autocomplete_options = {
        source: function(request,response) {
            var params = { term : request.term };
            if ($("#hat").val() !== "") {
                params.hat = $("#hat").val();
            }
            if ($("#active").val() !== "") {
                params.active = $("#active").val();
            }
            $.get('/admin/people/autocomplete',params,"json").then(
                function(data){
                    if (! data.length) {
                        name_element.data({id:""});
                    }
                    return response(data);
                }
            );
        },
        minLength: 2,
        select: function( event, ui ) {
            //console.warn(ui);
            event.preventDefault();
            name_element.val(ui.item.label).data({id:ui.item.value});
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
    };
    name_element.autocomplete(autocomplete_options);
    $("#hat, #active").on("change",function(event){
        if (name_element.val()) {
            name_element.autocomplete("search");
        }
    });
    var button = $("#btn-search");
    button.on("click",function(event){
        $.get("/admin/people/search");
    })
});
