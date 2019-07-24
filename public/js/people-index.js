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
    });
    var results_div = $("#results");
    $("body").on("click","nav .pagination a",function(event){
        event.preventDefault();
        var url = get_people_search_url($(this).text().trim());
        $.get(url).then(function(res){results_div.html(res);});
    });
    button.on("click",function(event)
    {
        event.preventDefault();
        var url = get_people_search_url();
        $.get(url).then(function(res){results_div.html(res);});

    });
});

const get_people_search_url = function(page){

    var name_element = $("#name");
    var id = name_element.data("id");
    var query;
    var path = `${window.document.location.pathname}`;
    if (id) {
        query `id=${id}`;
    } else {
        query =  $("#search-form").serialize();
    }
    if (! page) {
        page = 1;
    }
    return `${path}?${query}&page=${page}`;

}
