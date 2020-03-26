
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
$("#results").on(
    "defendants.loaded",
    function(e){ 
        console.log("'defendants.loaded' event triggered");
        $("#pagination > div").html($("#results nav"));
        if ($("#btn-add").attr("hidden")) {
            $("#btn-add").removeAttr("hidden");
        }
    }
);
$(function(){
    input.autocomplete(autocomplete_options);
    $("#error-div, #success-div").removeClass("mt-4");
    var flash_message = $("h2.navigation").next(".alert-success");
    if (flash_message.is(":visible")) {
        // get rid of the flash upon the next action
        $("#btn-add, #btn-search, #results, #pagination").one("click",()=>flash_message.hide());
    }
    var btn_add = $("#btn-add");
    btn_add.on("click",function(e){  
        var url = $(this).data().url;
        $("#div-form").load(url,(r)=>{
            $("div.card").removeAttr("hidden");
            $("div.card form").attr({action:url});
            $("div.card.alert-sucess").hide();
            $(".card-header h3").text("add name");
            $("#btn-submit").removeAttr("disabled");
        });
    });
    /* kind of warped, but the pagination needs to move 
      to another div.row or else the right-most links apparently 
      run under the right div.col, and don't work.
    */
    $("#results nav").appendTo($("#pagination > div"));
    $("#btn-search").on("click",function(e){
        var term = input.val().trim();
        if (! term) { return; }
        $.get("/defendants/search",{term}).then(
            res=>{
                $("#results").html(res).trigger("defendants.loaded");
            }
        );
    });    
    $("#pagination").on("click",".pagination a.page-link",function(e){
        e.preventDefault();
        $.get(this.href).then(res=>{
            $("#results").html(res).trigger("defendants.loaded");;
        });
    })
    $("#results").on("click","li a",function(e){
        e.preventDefault();
    })
    .on("click",".defendant-names li",function(e){
        var id = $(this).data("id");        
        $("#div-form").load(`/admin/defendants/edit/${id}`,
            ()=>{                
                $("div.card").removeAttr("hidden");
                $(".card-header h3").text("edit name");
                $("div.card.alert-sucess").hide();
                if (!$("#div-form form").data("has_related_entities")) {
                    $("#btn-delete").removeAttr("hidden");
                } else {
                    $("#btn-delete").attr("hidden",true);
                }
                $("#btn-submit").removeAttr("disabled");
                $("#div-form form").attr({action:`/admin/defendants/edit/${id}`});
                if ($("#success-div").is(":visible")) {
                    $("#success-div").slideUp();
                }                             
            }
        );
    });
});
