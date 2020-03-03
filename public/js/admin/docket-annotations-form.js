/* global  $, fail, displayValidationErrors */
$(function(){
    if ($("#docket").val()) {
        // hack the breadcrumb nav
        $("h2").first().children("a").get(1).href += "/"+$("#docket").val();
    }
    $("#btn-save").on("click", function(e){
            e.preventDefault();
            console.debug("boink! shit was clicked");
        }
    );
});
