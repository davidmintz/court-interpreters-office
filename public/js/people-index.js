var view = new Vue({

    el : "#results",
    data : {
        people  : [],
        pages : 0,
        total : 0
    },
    methods : {} //setPeople : function(people) { this.people = people;}

});

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
    results_div = $("#results");
    button.on("click",function(event){
        event.preventDefault();
        var params = {};
        var url = "/admin/people/search";
        var id = name_element.data("id"), query;
        if (id) {
            //console.log("id is "+id);
            query = "id="+id;
        } else {
            query = $("#search-form").serialize();
        }
        $.getJSON(url+"?"+query)
        .success(function(response){
            results_div.children(".status-message").text(response.count + " found").show();
            if (response.count) {
                var data = response.data;
                var p = [];
                for (var i in data) {
                    p.push(data[i][0]);
                }
                view.people = p;
            }
            view.total = response.count;
            view.pages = response.pages;
        });
    })
});
