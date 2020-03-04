/* global  $, fail, displayValidationErrors, formatDocketElement */
$(function(){
    var docket_element = $("#docket");
    docket_element.on("change",formatDocketElement).trigger("change");
    if (docket_element.val()) {
        // hack the breadcrumb nav
        $("h2").first().children("a").get(1).href += "/"+docket_element.val();
    }

    $("#btn-save").on("click", function(e){
            e.preventDefault();
            var form = $("#annotation-form");
            var method, url = form.attr("action");
            if (url.includes("update")) {
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
