$(function(){
    console.debug("gack");
    var autocomplete_field = $("#autocomplete-members");
    autocomplete_field.autocomplete({
        source: (request,response) => {
            var params = { term : request.term, active : 1 };
            $.get("/admin/people/autocomplete",params).then(
                (data,statusText,jqXHR) => response(data)
            );
        },
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            //$("#person-autocomplete").val(ui.item.value);
            console.log(`${ui.item.label}, id ${ui.item.value}`);
            $(this).val("");
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        }
    });
    $("#calendar").datepicker({
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        dateFormat : "yy-mm-dd",
        onSelect : function(date, instance){

        }
    });
});
