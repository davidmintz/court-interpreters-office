var $, displayValidationErrors;

$(function(){

    $("a.dropdown-item:contains('court closings')").addClass("active");

    // load closings for the year
    $(".court-closings").on("click",".closing-link",function(event,params){
        event.preventDefault();
        console.log("click event on "+$(this).attr("href"));
        var link = $(this);
        var href = link.attr('href');
        var list = link.parent().children("ul");
        var toggle = (params && params.toggle === false) ? false : true;
        console.log("our toggle parameter: "+(toggle ? "true":"false"));
        if (list.is(":visible") && toggle) {
            console.log("returning slideUp b/c list is visible and tog = true");
            return list.slideUp();
        }
        $.getJSON(href,function(data){
            if (! data) {
                return $(this).remove();
            }
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
            list.html(items);
            console.debug("shit is real");
            if (toggle) { //&& (! list.is(":visible")
                list.slideDown();
                console.log(list);
                console.log("we said slideDown()!");
            } else {
                console.debug("wtf? ");
            }
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
            `<p>The closing for <strong>${when}</strong> has been successfully ${action}.</p>`
        ).slideDown();
        $("#form-modal .validation-error").hide();
        if (action === "updated" || action === "deleted") {
            // close the modal automatically
            window.setTimeout(
                function(){$("#form-modal").modal("hide");},3000);
        } else {
            // it was an insert, so make it easy to continue
            // inserting
            if (! $(".insert-continue").length) {
                $("#status").append(
                    `<p class="insert-continue">To add more dates, update the
                    form below and save again.</p>`
                );
            }
        }
        // refresh the display
        var year = when.substring(7);
        var selector = `a.closing-link:contains(${year})`;
        var link = $(selector);
        console.debug(`link for ${year} exists? `+(link.length ? "yes":"no"));
        if (link.length) {
            return link.trigger("click",{toggle:false});
        }
        if (! link.length || ! $(".court-closings ul").length) {
            console.debug("no year-lists?");
            $(".court-closings").load(
                "/admin/court-closings .court-closings > ul",
                function(){
                    console.debug("all shit was reloaded, triggering a click");
                    $(selector).trigger("click",{toggle:false});
                }
            );
        }

    };
});
