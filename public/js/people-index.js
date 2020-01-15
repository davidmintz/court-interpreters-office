/* global $ */

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
                (data)=>response(data)
                // ,statusText,jqXHR
                // bullshit, experimental effort to handle non-json
                // responses e.g., redirects to /login
                // if (-1 < jqXHR.getResponseHeader("content-type")
                //         .toLowerCase().indexOf("application/json")) {
                //     if (! data.length) {
                //          name_element.data({id:""});
                //     }
                //     return response(data);
                // }
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
    });
    var results_div = $("#results");
    $("body").on("click","nav .pagination a",function(event){
        event.preventDefault();
        var page, m = this.href.match(/page=(\d+)/);
        if (m && m[1]) {
            page = m[1];
        }
        var url = get_people_search_url(page);
        $.get(url).then(function(res){results_div.html(res);});
    });
    button.on("click",function(event)
    {
        event.preventDefault();
        var url = get_people_search_url();
        $.get(url).then(function(res){results_div.html(res);});

    });
    /*  work in progress. it might be cool to show details for generic people in
        a popover. for people who have a user account, refer them to user admin
     */
    results_div.on("click","td a[title='view details']",function(){
        // if ($(this).text().trim() === "#") {
        //     e.preventDefault();
        // }
        if ($(this).data("user_role")) {
            //e.preventDefault();
            console.warn("so and so has default role: "+$(this).data("user_role"));
        }
    });
});

const get_people_search_url = function(page){
    var query =  $("#search-form").serialize();
    var path = `${window.document.location.pathname}`;
    if (! page) {
        page = 1;
    }
    return `${path}?${query}&page=${page}`;
};
