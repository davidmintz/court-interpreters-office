/* global  $, fail, displayValidationErrors, formatDocketElement */

var get_event_count = function(docket)
{
    return $.get(`/admin/docket-notes/api/count-events?docket=${docket}`);
}

$(function(){
    var docket_element = $("#docket");
    docket_element.on("change",function(e){
        formatDocketElement(e);
        if (docket_element.data("valid")) {
            get_event_count(docket_element.val())
            .then((res)=>{
                if (!window.parseInt(res.count)) {
                    displayValidationErrors({docket : { "bullshit": "no events have this docket number"}});
                    docket_element.addClass("is-invalid");
                }
            });
        }
    }).trigger("change");
    if (docket_element.val()) {
        // hack the breadcrumb nav
        $("h2").first().children("a").get(1).href += "/"+docket_element.val();
    }
    $("#btn-delete").on("click",function (e){
        if (!window.confirm("Are you sure you want to delete this docket annotation?")) {
            return;
        }
        var id = $(`input[name="id"]`).val();
        var url = `/admin/docket-notes/api/delete/${id}`;
        var method = "DELETE";
        console.debug(`gonna ${method} to ${url}`);
        $.ajax({url, method,
            headers:{ "X-Security-Token":$(`input[name="csrf"]`).val()}
        })
        .then((res)=>{
            console.log(res);
            if ($("#status").is(":visible")) {
                $("#status").hide();
            }
            $("#annotation-form").replaceWith(
                `<div style="max-width:400px" class="alert alert-success my-4">This annotation for docket number ${res.entity.docket} has been deleted.</div>`
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
            var btn = $(this);
            $.ajax({ url, method, data})
            .then((res)=>
                {
                    if (res.validation_errors) {
                        return displayValidationErrors(res.validation_errors);
                    }
                    console.debug(res);
                    $("#status").text("Annotation saved.").show();
                    btn.attr({disabled:true});
                    var textarea = $(`textarea[name="comment"]`);
                    var parent = textarea.parent();
                    // display the returned annotation text in a div
                    // to replace the textarea
                    var div = $(`<div class="mt-0 py-2 px-2 border border-primary rounded">${res.entity.comment}</div>`);
                    div.css({minHeight: `${parent.height()}px`,cursor:"pointer" })
                        .one("click", function(e){ form.trigger("change"); });
                    textarea.hide();
                    parent.prepend(div);
                    // restore the textarea if they want to edit again
                    form.one("change",()=>{
                        if (parent.children("div.border").length) {
                            textarea.show();
                            parent.children("div.border").remove();
                        }
                        if ($("#status").is(":visible")) {
                            $("#status").slideUp(function(){$(this).text("")});
                        }
                        btn.removeAttr("disabled");
                    });
                }
            );
        }
    );
});
