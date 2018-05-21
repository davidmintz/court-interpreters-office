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
        change : function(event, ui) {
            console.log("autocomplete change event fired")
            var id = $(this).data("id");
            console.log(id ? "id is "+id : "NO id is set");
        },
        open : function(event, ui) {
            console.log("autocomplete OPEN event fired. unsetting id");
            $(this).data("id",null);
        }
    };
    name_element.autocomplete(autocomplete_options);
        //.on("change",function(){ console.log("name changed"); });
    $("#hat, #active").on("change",function(event){
        if (name_element.val()) {
            name_element.autocomplete("search");
        }
    });
    results_div = $("div.results");
    button.on("click",function(event){
        event.preventDefault();
        var params = {};
        var url = "/admin/people/search";
        console.log("click");
        var id = name_element.data("id");
        if (id) {
            console.log("id is "+id);
            $.get("/admin/people/search?id="+id,"json")
            .success(function(data){
                results_div.html(data.length + " found");
            });
            return;
        } else {
            console.log("no id attrib in name element");
        }
        $.get("/admin/people/search?",$("#search-form").serialize(),"json")
            .success(function(response){

                results_div.html(response.count + " rows found");
                if (response.count) {
                    var people = [];
                    for (var i in response.data) {
                        var p = response.data[i][0];
                        people.push(p.lastname,+", "+p.firstname);
                    }
                    results.append
                }
                //for (var i in window.my_data.data) { console.log(  window.my_data.data[i][0].lastname ) }

            });
    })
});
