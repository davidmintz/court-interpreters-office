var $, formatDocketElement, displayValidationErrors;

$(function(){
    $("input.docket").on("change",formatDocketElement);

    var btn = $("#btn-submit");
    var form = btn.closest("form");
    $("#btn-submit").on("click",function(e){
        $.get(form.attr("action"),form.serialize())
        .done((res, status, xhr)=>{
            $(".validation-error").hide();
            //console.log(`responseJSON is a: ${ typeof xhr.responseJSON}`);
            //console.debug(`content-type is ${xhr.getResponseHeader('content-type')}`);
            if ( xhr.responseJSON && !res.valid ) {
                return displayValidationErrors(res.validation_errors);
            }
            $("#results").html(res);
        })
        .fail((data)=>{console.warn("shit happened");fail(data)});
    });
});
