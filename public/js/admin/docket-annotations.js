/* global  $, fail, formatDocketElement, displayValidationErrors */
$(function(){
    var url = document.location.pathname;
    console.debug(url);
    $(".docket").on("change",formatDocketElement);
    $("#btn-search").on("click",function(e){
        var el = $("#docket");
        if (!el.data("valid") || ! el.val().trim()) {
            return;
        }
        var docket = el.val();
        console.debug(`${url}/${docket}`);
        $.get(`${url}/${docket}`)
        .then((res)=>{

        });

    });

    // $("#btn-create").on("click",function(e){});

});
