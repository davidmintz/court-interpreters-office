/* global  $, fail, formatDocketElement, displayValidationErrors */
$(function(){
    var url = window.basePath + "/admin/docket-annotations";
    console.debug(url);
    $(".docket").on("change",formatDocketElement).trigger("change");
    var el = $("#docket");
    $("#btn-search").on("click",function(e){
        if (!el.data("valid") || ! el.val().trim()) {
            return;
        }
        var docket = el.val();
        $.get(`${url}/${docket}`)
        .then((res)=>{$("#results").html(res);})
        .fail(fail);

    });

    $("#btn-create").on("click",function(e){
        e.preventDefault();

        if (el.val().trim() && el.data("valid")) {
            console.log("use "+el.val());
            url += el.val().trim();
        } else {
            console.log("use "+document.location.pathname);
        }
    });

});
