$(function(){

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
            console.log("fuck is going on?");
            console.log(`append: ${ui.item.label}, id ${ui.item.value}`);
            if ($(`#members input[value="${ui.item.value}"]`).length) {
                //alert("You already have this person in the rotation");
                $(this).val("");
                return;
            }
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
    })// prevent unwanted, mysterious click event from firing on .btn-remove
    .on("keypress",(e)=>{ if (e.which === 13) { e.preventDefault();} });

    autocomplete_field.autocomplete("instance")._renderItem =
     function(ul, item) {
        return $( "<li>" )
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
        dateFormat : "DD dd-M yy"//,
        // onSelect : function(date, instance){
        //     console.log(date);
        // }
    });
    $("#members").on("click","button.btn-remove-item",function(e){
        e.preventDefault();
        var div = $(this).parent();
        div.slideUp(()=>{
            div.remove();
            $("#member-sort-help").attr({hidden:()=>$("#members li").length < 2});
        });
    })
    .sortable();

    $("#btn-save").on("click",function(e){
        e.preventDefault();
        var form = $(this).closest("form");
        url = form.attr("action");
        $.post(url,form.serialize()).then(
            res=>{console.log(res);}
        );
    });
});
