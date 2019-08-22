var $, formatDocketElement, displayValidationErrors;

$(function(){
    $("input.docket").on("change",formatDocketElement);

    var btn = $("#btn-submit");
    var form = btn.closest("form");
    $("#btn-submit").on("click",function(e){
        $.get(form.attr("action"),form.serialize())
        .done((res, status, jqXHR)=>{
            $(".validation-error").hide();
            console.debug(jqXHR.getResponseHeader('content-type'));
            if (! res.valid ) {
                return displayValidationErrors(res.validation_errors);
            }
        })
    });
});
