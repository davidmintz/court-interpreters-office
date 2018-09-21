var $, displayValidationErrors, formatDocketElement, parseTime;

var appendDefendant = function(data)
{
    var html =
            `<li id="deft-${data.value}" class="list-group-item pr-0 py-0">
            <span class="float-left pt-2">${data.label}</span>
            <input type="hidden" name="request[defendants][]" value="${data.value}">
            <button class="btn btn-warning btn-remove-item float-right border" title="remove this defendant">
            <span class="fas fa-times" aria-hidden="true"></span>
            <span class="sr-only">remove this defendant
            </span></button>
            </li>`;
    $("#defendants").append(html);
};

$(function(){
    //<input name="user[judges][]" value="2550" type="hidden">
    const form = $("#request-form");
    const is_update = form.attr("action").indexOf("create") === -1;

    const deftname_autocomplete_options = {
        source: "/defendants/autocomplete",
        minLength: 2,
        select: (event, ui) => {
            //var that = $(this);
            event.preventDefault();
            appendDefendant(ui.item);
            $("#defendant-search").val("");
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
        open : function() {
            // if (slideout.is(":visible")) {
            //     slideout.hide();
            // }
        }
    };
    $("#defendant-search").autocomplete(deftname_autocomplete_options);

    $("#defendants").on("click",".btn-remove-item",
        function(event){
            event.preventDefault();
            $(this).closest(".list-group-item").slideUp(
                function(){ $(this).remove();} );
        }
    );

    $("#time").on("change",parseTime);

    $("#docket").on("change",formatDocketElement);
});
