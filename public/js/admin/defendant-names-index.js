
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
    }).on("click","li a",function(e){
        e.preventDefault();
    })
    .on("click",".defendant-names li",function(e){
        var id = $(this).data("id");
        // document.location = `${window.basePath}/admin/defendants/edit/${id}`;
        $("#div-form").load(`/admin/defendants/edit/${id} form`,
            ()=>{
                $("div.card").removeAttr("hidden");
                if (!$("#div-form form").data("has_related_entities")) {
                    $("#btn-delete").removeAttr("hidden");
                } else {
                    $("#btn-delete").attr("hidden",true);
                }
                $("#div-form form").attr({action:`/admin/defendants/edit/${id}`});
            }
        );
    });
});
