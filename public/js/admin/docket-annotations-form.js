/* global  $, fail, displayValidationErrors, formatDocketElement */
$(function(){
    var docket_element = $("#docket");
    docket_element.on("change",formatDocketElement).trigger("change");
    if (docket_element.val()) {
        // hack the breadcrumb nav
        $("h2").first().children("a").get(1).href += "/"+docket_element.val();
    }
    $("#btn-delete").on("click",function (e){
        if (!window.confirm("Are you sure you want to delete this docket annotation?")) {
            return;
        }
        var id = $(`input[name="id"]`).val();
        var url = `${window.basePath}/admin/docket-notes/api/delete/${id}`;
        var method = "DELETE";
        console.debug(`gonna ${method} to ${url}`);
        $.ajax({url, method,
            headers:{ "X-Security-Token":$(`input[name="csrf"]`).val()}
        })
        .then((res)=>{
            console.log(res);
            $("#annotation-form").replaceWith(
                `<div style="max-width:400px" class="alert alert-success my-4">This docket annotation has been deleted.</div>`
            );
        });
    });
    $("#btn-save").on("click", function(e){
            e.preventDefault();
            var form = $("#annotation-form");
            var method, url = form.attr("action");
            if (url.includes("update")) {
                var id = $(`input[name="id"]`).val();
                url += `/${$("input[name='id']").val()}`;
                method = "PUT";
            } else {
                method = "POST";
            }
            data = form.serialize();
            console.debug(`gonna ${method} to ${url}`);
            $.ajax({ url, method, data})
            .then(
                (res)=>{
                    if (res.validation_errors) {
                        return displayValidationErrors(res.validation_errors);
                    }
                    console.debug(res);

                }
            );
        }
    );
});
