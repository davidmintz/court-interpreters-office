$(function(){
        var button = $("#btn-search");
        var name_element = $("#name");
        var autocomplete_options = {
            source: function(request,response) {
                var params = { term : request.term };
                if ($("#hat").val() !== "") {
                    params.hat = $("#hat").val();
                }
                if ($("#active").val() !== "") {
                    params.active = $("#active").val();
                }
                $.get("/admin/people/autocomplete",params,"json").then(
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
                event.preventDefault();
                name_element.val(ui.item.label).data({id:ui.item.value});
            },
            focus: function(event,ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
            },
            open : function() {
                //console.log("autocomplete OPEN event: unsetting id");
                $(this).data("id",null);
            }
        };
        name_element.autocomplete(autocomplete_options);
        $("#hat, #active").on("change",function(){
            if (name_element.val()) {
                name_element.autocomplete("search");
            }
            //view[($(this).attr("id"))] = $(this).val() || null;
        });
        var results_div = $("#results");
        $("#pagination").on("click","a",function(event){
            event.preventDefault();
            button.trigger("click",{url:this.href});
        });
        button.on("click",function(event,params)
        {
            event.preventDefault();
            var url;
            var page = button.data("page")|| 1;
            //console.log("page is: "+button.data("page"));
            if (! params || ! params.url) {
                url = "/admin/people/search";
                var id = name_element.data("id");
                var query;
                if (id) {
                    query = "id="+id;
                } else {
                    query = $("#search-form").serialize();
                }
                url += `?${query}&page=${page}`;
            } else {
                url = params.url;
            }
            $.get(url).then(function(res){

            });
            // $.getJSON(url).success(function(response)
            // {
            //     var people = [];
            //     if (response.count) {
            //         var data = response.data;
            //         for (var i in data) {
            //             var person = data[i][0];
            //             person.hat = data[i].hat;
            //             people.push(person);
            //         }
            //         //view.not_found = false;
            //         if (id) { // unset the name element's person-id
            //             name_element.data({id : null });
            //         }
            //     } else {
            //         view.not_found = true;
            //     }
            //     // view.people = people;
            //     // view.pages = response.pages;
            //     // view.url = url.replace(/&page=\d+/,"");
            //     button.data({page : 1});
            //     $('#results .status-message').show();
            //     $("li div.details").remove();
            // });
            $("#btn-add-person").show();
        });


});
