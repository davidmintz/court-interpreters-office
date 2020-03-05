/* global  $, fail, formatDocketElement, displayValidationErrors */
$(function(){
    var url = "/admin/docket-annotations";
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
    $(".btn-delete").on("click",function(e){
        e.preventDefault();
        if (! window.confirm("Are you sure you want to delete this annotation?"))
        { return; }
        var tr = $(this).closest("tr");
        var id = tr.data("id");
        var url = `/admin/docket-notes/api/delete/${id}`;
        var method = "DELETE";
        var csrf = $("#results").data("csrf");
        console.debug(`gonna ${method} to ${url}`);
        $.ajax({
            headers:{ "X-Security-Token": csrf },
            url, method
        }).then((res)=>{
            tr.addClass("text-muted").css({textDecoration:"line-through"});
            $("#verbiage").text("1 annotation deleted");
        }).fail(fail);
    });
    $("#btn-create").on("click",function(e){
        e.preventDefault();
        if (el.val().trim() && el.data("valid")) {
            var docket = el.val().trim();
            // console.debug(`redirect to ${url}/${docket}/add`);
            document.location = `${url}/${docket}/add`;
        } else {
            // console.debug(`redirect to ${url}/add`);
            document.location =  `${url}/add`;
        }
    });
});
