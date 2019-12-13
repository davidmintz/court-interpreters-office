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
            console.log(`${ui.item.label}, id ${ui.item.value}`);
            $("#members").append(
                `<li class="list-group-item pr-1 py-1"><span class="float-left deft-name align-middle pt-1">${ui.item.label}</span>
                <input name="members[]" value="${ui.item.value}" type="hidden"><button class="btn btn-warning btn-sm btn-remove-item float-right border" title="remove from rotation">
                <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">remove</span></button></li>`
            );
            $(this).val("");
            // var hidden =  $("#members li").length < 2;
            $("#member-sort-help").attr({hidden:()=>$("#members li").length < 2});
        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        }
    });
    autocomplete_field.autocomplete("instance")._renderItem =
     function(ul, item) {
        return $( "<li>" )
            .attr( "data-hat", item.hat )
            .attr("title",item.hat)
            .attr("data-id",item.id)
            .append( $( "<div>" ).html( `${item.label} <span class="text-muted">${item.hat}</span>` ) )
            .appendTo( ul );
     };
    $("#datepicker_start_date").datepicker({
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        minDate: 0,
        constrainInput : true,
        altField: "#start_date",
        altFormat : "yy-mm-dd",
        dateFormat : "DD dd-M yy",
        onSelect : function(date, instance){
            console.log(date);
        }
    });
    $("#members").sortable()
    .on("click",".btn-remove-item",function(e){
        e.preventDefault();
        $(this).parent().remove();
        $("#member-sort-help").attr({hidden:()=>$("#members li").length < 2});
    });
});
