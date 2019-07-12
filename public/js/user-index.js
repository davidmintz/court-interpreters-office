/** public/js/user-index.js */

$(function(){
    var placeholders = {
        name : "last name[, first name]",
        email : "user's email address",
    };
    $("#search-by").on("change",function(){
        var search_by = $(this).val();
        if ("judge" === search_by) {
            if ($("#user").is(":visible")) {
                $("#judge, #user").toggle();
            }
        } else {
            $("#user").attr({placeholder: placeholders[search_by]});
            if ($("#judge").is(":visible")) {
                $("#judge, #user").toggle();
            }
        }
    });
    $("#user").autocomplete({
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
            console.log(ui.item);// to be continued
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
    });
    $('#button-search').on("click",function(e){
        var term;
        if ("judge" === $("#search-by").val()) {
            term = $("#judge").val();
        } else {
            term = $("#user").val().trim();
        }
        if (! term) {
            $("#input-validation").text("search text is required").attr({hidden:false});
            return;
        }
        $("#input-validation").attr({hidden:true});
        $.get(document.location.pathname,{
            term, search_by : $("#search-by").val().trim(),
        }).then(
            (response) => {
                $("#results").html(response);
            }
        );

    });
    if ($("#user").val().trim().length || $("#judge").val()) {
        console.log("we have defaults");
    }
});
