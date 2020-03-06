
var autocomplete_options = {
    source: "/defendants/autocomplete",
    minLength: 2,
    select: function( event, ui ) {
        event.preventDefault();
        $("#btn-search").trigger("click");
    },
    focus: function(event,ui) {
        event.preventDefault();
        $(this).val(ui.item.label);
    },
};
var input = $("#defendant-autocomplete");
$(function(){
    input.autocomplete(autocomplete_options);
    $("#btn-search").on("click",function(e){
        var term = input.val().trim();
        if (! term) { return; }
        $.get("/defendants/search",{term}).then(
            res=>{$("#results").html(res);}
        );
    });
    $("#results").on("click",".pagination a.page-link",function(e){
        e.preventDefault();
        $.get(this.href).then(res=>$("#results").html(res));
    })
    .on("click",".defendant-names li",function(e){
        var id = $(this).data("id");
        document.location = `${window.basePath}/admin/defendants/edit/${id}`;
    });
});
