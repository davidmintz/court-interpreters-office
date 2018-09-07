var $, displayValidationErrors;

$(function(){

    $("a.dropdown-item:contains('court closings')").addClass("active");

    $(".closing-link").on("click",function(event){
        event.preventDefault();
        var link = $(this);
        var href = link.attr('href');
        var list = link.parent().children("ul");
        if (list.is(":visible")) {
            return list.slideUp();
        }
        $.getJSON(href,function(data){
            var items  = data.map(obj => {
                var el = $("<li>").addClass("list-group-item");
                var str = obj.date.date.substring(0,10);
                var date = moment(str,"YYYY-MM-DD").format("dddd MMMM D");
                var link = $("<a>").attr({href : document.location.pathname + "/edit/"+obj.id})
                var text = obj.holiday ? obj.holiday.name : obj.description_other;
                link.text(date);
                el.html(link).append(' - ' +  text);
                return el;
            });
            list.html(items).slideDown();
        });
    });

    $("#btn-add").on("click",function(){
        $("#form-modal .modal-body").load('/admin/court-closings/add form',
        function() {
            $("#form-label").text("add a court closing");
            $("#form-modal").modal("show");
            $("#date").datepicker({});
        });
    });


    $("ul").on("click","li ul li a",function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        $("#form-modal .modal-body").load(url +' form',
            function(){
                $("#form-label").text("edit court closing");
                $("#form-modal").modal("show");
                $("#date").datepicker({});
            }
        );
    });
    $("#form-modal").on("change","#holiday",function(){
        if ($(this).val() === "other") {
            $("#form-modal div.description_other").slideDown(
                function(){
                    $("#description_other").focus();
                }
            );
        } else {
            $("#form-modal div.description_other").slideUp();
        }
    });

    $("#btn-save").on("click",function(){
        var form = $("#form-modal form");
        var url = form.attr("action");
        console.log("submitting to "+url);
        $.post(url,form.serialize())
            .then(function(response){
                if (response.validation_errors) {
                    return displayValidationErrors(response.validation_errors);
                }
                console.log("saved entity");
        });
    });


});
