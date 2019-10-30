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
        $(event.target).trigger("change");
    }
};

const interpreter_autocomplete_opts = {
    source: "/admin/interpreters",
    minLength: 2,
    select: (event, ui) => {
        event.preventDefault();
        $("#interpreter_id").val(ui.item.id);
        $(event.target).trigger("change");
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
    //console.log(`referer: ${document.referer}`);
    if (docket_input.val()) {
        docket_input.trigger("change");
    }
    $("input.date").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange : "-20:+4",
        selectOtherMonths : true,
        showOtherMonths : true,
    });
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
    })
    /**
     * toggles display of additional defendant-names
     */
    .on("click", "a.expand-deftnames", function(e){
        e.preventDefault();
        $(this).hide().siblings().slideDown();
    })
    .on("click","a.collapse-deftnames", function(e){
        e.preventDefault();
        var self = $(this);
        self.hide().siblings().not(":first-of-type").slideUp(
            function(){self.siblings("a.expand-deftnames").show();}
        );
    });
    $('.event-delete').on("click",function(e){
        e.preventDefault();
    });
    $("#interpreter").autocomplete(interpreter_autocomplete_opts);
    var btn_reset = $("#btn-reset");
    form.on("change",function(e){
        var target = $(e.target);
        var what = target.attr("name");
        if (["submit","order"].includes(what)) {
            return;
        }
        var has_defaults, elements = form.find('select, input');
        elements.each(function(){
            var e = $(this);
            if (e.val() && ! ["submit","order"].includes(e.attr("name"))) {
                //console.log(`${$(this).attr("name")} has a truthy value`);
                has_defaults = true;
            }
        });
        if (has_defaults) {
             btn_reset.removeAttr("hidden")
        } else {
            btn_reset.attr({hidden:true});
        }
    });
    btn_reset.on("click",function(e){
        e.preventDefault();
        $("#results").empty();
        form.find('select, input').each(function(i,e){
            if (!["submit","order"].includes(e.name)) {
                $(this).val("");
            }
        });
        $.get("/admin/search/clear").then(()=>btn_reset.attr({hidden:true}));
    });
});
