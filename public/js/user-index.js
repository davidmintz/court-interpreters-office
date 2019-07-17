/** public/js/user-index.js */

$(function(){
    var placeholders = {
        name : "last name[, first name]",
        email : "user's email address",
    };
    $("#search-by").on("change",function(){
        var search_by = $(this).val();
        if ("judge" === search_by) {
            if ($("#term").is(":visible")) {
                $("#judge, #term").toggle();
            }
        } else {
            $("#term").attr({placeholder: placeholders[search_by]});
            if ($("#judge").is(":visible")) {
                $("#judge, #term").toggle();
            }
        }
    });
    $("#term").autocomplete({
        source : function(request,response) {
            var search_by = $("#search-by").val().trim();
            $.getJSON(`${window.basePath}/admin/users/autocomplete`,{
                search_by, term: request.term
            })
            .then((data)=>{
                response(data);
            })
            .fail(fail);
        },
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            console.log(ui.item);
            $('#button-search').trigger("click");
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
    });
    $('#button-search').on("click",function(e){
        params = get_search_parameters();
        if (! params.term) {
            $("#input-validation").text("search text is required").attr({hidden:false});
            return;
        }
        $("#input-validation").attr({hidden:true});
        $.get(document.location.pathname,params).then(
            response => {$("#results").html(response);$(`[data-toggle="tooltip"]`).tooltip();}
        );
    });
    $("#results").on("click","a.page-link",function(e){
        e.preventDefault();
        $.get(this.href,get_search_parameters()).then(
            response=>{$("#results").html(response);$(`[data-toggle="tooltip"]`).tooltip();}
        );
    });
    $(`[data-toggle="tooltip"]`).tooltip();
});

var get_search_parameters = function(){
    var term;
    if ("judge" === $("#search-by").val()) {
        term = $("#judge").val();
    } else {
        term = $("#term").val().trim();
    }
    return {term,search_by : $("#search-by").val().trim()};
};
