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
    var datepickerOptions = {
        changeMonth : true,
        changeYear : true,
        selectOtherMonths: true,
        showOtherMonths: true
    };

    $("#btn-add").on("click",function(){
        $("#form-modal .modal-body").load('/admin/court-closings/add form',
        function() {
            $("#form-label").text("add a court closing");
            $("#btn-delete").hide();
            $("#form-modal").modal({ focus : false, backdrop : false });
        });
    });
    $("#form-modal").on("shown.bs.modal",function(){
        $("#date").datepicker(datepickerOptions);
    });

    // load the editing form when the closing label is clicked
    $("ul").on("click","li ul li a",function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        $("#form-modal .modal-body").load(url +' form',
            function(){
                $("#form-label").text("edit court closing");
                $("#btn-delete").show();
                $("#form-modal").modal({ focus : false, backdrop : false });
            }
        );
    });

    // toggle display of the "other" text field depending on whether it's
    // required
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

    $('#form-modal').on("click","#btn-delete", function(){
        if (! confirm("Are you sure you want to delete this closing?")) {
            return;
        }
        var url = $("#form-modal form").attr("action").replace("edit","delete");
        $.post(url).fail(fail).done(function(response) {
            success(response, "deleted");
        });
    });

    // submit the form for edit|add
    $("#btn-save").on("click",function(){
        var form = $("#form-modal form");
        var url = form.attr("action");
        console.log("submitting to "+url);
        var action = url.indexOf("add") === -1 ? "updated" : "added";
        $.post(url,form.serialize())
        .fail(fail)
        .done(function(response) { success(response, action); });
    });

    /** error handler */
    var fail = function(response){
        var html = `<p>Sorry, we've encountered an unexpected error.`;
        if (response.responseJSON && response.responseJSON.message) {
            html += ` The message was: ${response.responseJSON.message}`;
        }
        html += `</p><p>Please consult your site administrator for help</p>`;
        $("#status").removeClass('alert-success')
        .addClass('alert-warning').html(html)
        .show();
    };

    /** callback on successful request -- irrespective of validation errors */
    var success = function(response, action)
    {
        if (response.validation_errors) {
            return displayValidationErrors(response.validation_errors);
        }
        var when = moment($("#date").datepicker( "getDate" ))
            .format("DD-MMM-YYYY");
        $("#status").html(
            `<p>The closing for <strong>${when}</strong> has been successfully ${action}</p>`
        ).slideDown();
        $("#form-modal .validation-error").hide();
        if (action === "updated" || action === "deleted") {
            // close the modal automatically
            window.setTimeout(
                function(){$("#form-modal").modal("hide");},3000);
        } else {
            // make it easy to continue
            $("#status").append(
                `<p>To add more dates, update the form below and
                    save again.</p>`
            );
        }
    };

});
