var $, Vue;
var view = new Vue({

    el : "#results",
    data : {
        people  : [],
        pages : {
            pageCount : 0
        },
        current : 0,
        total : 0,
        url : "",
        hat : false,
        active : null,
        not_found : false
    },
    methods : {
        // checking whether you can do this
        test : function(){console.log(typeof $);}
    },
    computed : {
        from : function() {
            return (this.current - 1) * this.pages.itemCountPerPage + 1;
        },
        to : function() {
            return this.from + this.pages.currentItemCount - 1;
        }
    }
});

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
            //console.warn(ui);
            event.preventDefault();
            name_element.val(ui.item.label).data({id:ui.item.value});
            //button.trigger("click");
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
        open : function() {
            //console.log("autocomplete OPEN event fired. unsetting id");
            $(this).data("id",null);
        }
    };
    name_element.autocomplete(autocomplete_options);
    $("#hat, #active").on("change",function(){
        if (name_element.val()) {
            name_element.autocomplete("search");
        }
        view[($(this).attr("id"))] = $(this).val();
    });
    var results_div = $("#results");
    $("#pagination").on("click","a",function(event){
        event.preventDefault();
        var link = $(this);
        var page = parseInt(link.text());
        if (! page) {
            page = link.hasClass("first") ? 1 : view.pages.last;
        }
        button.data({page:page});
        button.trigger("click");
    });
    button.on("click",function(event)
    {
        event.preventDefault();
        var url = "/admin/people/search";
        var page = button.data("page") || 1;
        var id = name_element.data("id"), query;
        if (id) {
            //console.log("id is "+id);
            query = "id="+id;
        } else {
            query = $("#search-form").serialize();
        }
        url += "?"+query;
        $.getJSON(url+"&page="+page).success(function(response)
        {
            //results_div.children(".status-message").text(response.count + " found.").show();
            if (response.count) {
                var data = response.data;
                var p = [];
                for (var i in data) {
                    var person = data[i][0];
                    person.hat = data[i].hat;
                    p.push(person);
                }
                view.people = p;
                view.current = page;
                view.not_found = false;
            } else {
                //$("p.status-message").text("We found nobody in the database matching the above criteria.").show();
                view.people = [];
                view.current = 0;
                view.not_found = true;
            }
            view.total = response.count;
            view.pages = response.pages;
            view.url = url;
            button.data({page : null});
            $('#results .status-message').show();
        });
    });
    if ($("#search-form").data("session_defaults")) {
        button.trigger("click");
    }
});
