/** autocompletion options for defendant name search */

var formatDocketElement;

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
    var docket_input = $("input.docket");
    docket_input.on("change",formatDocketElement);
    // and/or check the referer? we have been redirected from
    // the /search/docket/<docket>
    console.log(`referer: ${document.referer}`);
    if (docket_input.val()) {
        docket_input.trigger("change");
    }
    $("input.date").datepicker({});
    $("#defendant-name").autocomplete(deft_autocomplete_opts)
    var btn = $("#btn-submit");
    var form = btn.closest("form");
    form.append($("<input>").attr({type:"hidden",name:"pseudo_judge",id:"pseudo_judge"}));
    var judge = $("#judge");
    judge.on("change",()=>{
        if (judge.val()) {
            $("#pseudo_judge").val(
                judge.children(":selected").data("pseudojudge") ? 1 : 0
            );
        }
    }).trigger("change");
    $("#btn-submit").on("click",function(e){
        e.preventDefault();
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
    const content = $("#results");
    content.on("click", ".pagination a",function(e){
        e.preventDefault();
        var page, m = this.href.match(/page=(\d+)/);
        if (m && m[1]) {
            page = m[1];
        } else {
            page = 1;
        }
        var path = form.attr("action");
        $.get(`${path}?${form.serialize()}&page=${page}`)
        .done(function(html){
            content.html(html);
        })
        .fail(fail);
    });
    $('.event-delete').on("click",function(e){
        e.preventDefault();
    });

});
