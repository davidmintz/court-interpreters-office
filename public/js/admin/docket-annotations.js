/* global  $, fail, formatDocketElement, displayValidationErrors */
$(function(){
    $(".docket").on("change",formatDocketElement);
    $("#btn-search").on("click",function(e){
        console.log("boink. you clicked the button.");
    });
});
