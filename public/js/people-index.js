$(function(){
    var button = $("#btn-search");
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
            //button.trigger("click");
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
    button.on("click",function(event){
        var params = {};
        var url = "/admin/people/search";
        var id = name_element.data("id");
        if (id) {
            $.get("/admin/people/search?id="+id,"json")
            .success(function(data){
                console.log(data);
            });
        }
        var hat = $("#hat").val();
        if (hat) {
            params.hat = hat;
        }
        var status = $("#status").val();
        if ( status !== "") {
            params.status = status;
        }
        //$.get("/admin/people/search");
    })
});
