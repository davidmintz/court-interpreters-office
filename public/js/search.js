var $, formatDocketElement, displayValidationErrors;
/** autocompletion options for defendant name search */
const deft_autocomplete_opts = {
    source: "/defendants/autocomplete",
    minLength: 2,
    select: (event, ui) => {
        event.preventDefault();
        $("#defendant-search").val("");
    },
    focus: function(event,ui) {
        event.preventDefault();
        $(this).val(ui.item.label);
    }
};
$(function(){
    $("input.docket").on("change",formatDocketElement);
    $("input.date").datepicker({});
    $("#defendant-name").autocomplete(deft_autocomplete_opts)
    var btn = $("#btn-submit");
    var form = btn.closest("form");
    form.append($("<input>").attr({type:"hidden",id:"pseudo_judge"}));
    var judge = $("#judge");
    judge.on("change",()=>{
        if (judge.val()) {
            $("#pseudo_judge").val(
                judge.children(":selected").data("pseudojudge") ? 1 : 0
            );
        }
    }).trigger("change");
    $("#btn-submit").on("click",function(e){
        $.get(form.attr("action"),form.serialize())
        .done((res, status, xhr)=>{
            $(".validation-error").hide();
            if ( xhr.responseJSON && !res.valid ) {
                return displayValidationErrors(res.validation_errors);
            }
            $("#results").html(res);
        })
        .fail(fail);
    });
    $("#results").on("click", ".pagination a",function(e){
        e.preventDefault();
        $.get(this.href,form.serialize())
        .done(function(html){
            $("#results").html(html);
        })
        .fail(fail);
    });
});
