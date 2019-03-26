const default_autocomplete_placeholder = "start typing last name...";
$(function(){
    // decide whether to display a suggestion that they send an email
    // about a noteworthy update
    var email_flag = false;
    if ( $('span.interpreter').length && $("ins, del").length) {
        var noteworthy = ["date","time","type","interpreters","location"];
        $("ins, del").each(function(){
            var field = ($(this).parent().prev("div").text().trim());
            if (noteworthy.includes(field)) {
                email_flag = true;
                return false;
            }
        });
    }
    console.log(`email flag? ${email_flag}`);// to be continued

    $("#btn-email, .btn-add-recipient").on("click",function(e){e.preventDefault();});
    $("#email-dialog").on("shown.bs.modal",function(event){
        $("#recipient-autocomplete").autocomplete({
            source: function(request,response) {
                var params = { term : request.term, active : 1, value_column : "email" };
                // to do: error handling
                $.get("/admin/people/autocomplete",params,"json").then(
                    function(data){return response(data);}
                );
            },
            minLength: 2,
            select: function( event, ui ) {
                event.preventDefault();
                var html = create_recipient(ui.item.value, ui.item.label);
                $(".email-subject").before(html);
                $("span.email-recipient").tooltip();
                $(this).val("");
                $("#btn-send").removeClass("disabled").removeAttr("disabled")
            },
            focus: function(event,ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
            }
        })
        /* the idea is disable the "+" until the manually-entered text is valid,
            i.e., '[recipient name] <email>'
         */
        .on("input",function(){
            // better yet:  detect whether a lookup is in progress
            // and if not, validate the input
            console.log(`input is happening with ${$(this).val()|| "(not shit)"}`)
            if ($("ul.ui-autocomplete:visible").length) {
                console.log("shit is visible");
                return;
            }
            // validate the input

        });
    });
    $("#email-dialog").on("show.bs.modal",function(event){
        // enable tooltips when dialog is shown
        $(`#${this.id} .btn`).tooltip();
        // this needs to be reconsidered...
        // $("#email-dialog a.dropdown-toggle, #email-dropdown, #email-dropdown .form-group").show();
        // $("#email-dialog a.dropdown-toggle, #email-dropdown .form-group").show();
        if ( !$("#email-dropdown input:visible").length ) {
            // console.log("no inputs to see, therefore hiding dropdown shit");
            // $(".dropdown-toggle").hide(); //, .dropdown-menu
        }
    })
    // remove form row (email recipient)
    .on("click",".btn-remove-item",function(event){
        event.preventDefault();
        $(this).tooltip("hide");
        var div = $(this).closest(".form-row");
        div.slideUp(()=> {
            div.remove();
            // if no recipients, disable "send" button
            if (! $(`input.email-recipient[name="to[]"]`).length) {
                $("#btn-send").addClass("disabled").attr("disabled");
            }
        });
    })
    .on("change", "select.email-header",function(){
        var input = $(this).parent().next().find("input.email-recipient");
        var name = input.attr("name") ===  "to[]" ? "cc[]" : "to[]";
        input.attr({name});
        if (! $(`input.email-recipient[name="to[]"]`).length) {
            $("#btn-send").addClass("disabled").attr("disabled");
        }
    })
    // close dialog
    .on("click","#btn-cancel",function(){
        $(".modal-header button.close").trigger("click");
    });
    $("#btn-add-recipients").on("click",function(e){
        // https://getbootstrap.com/docs/4.3/components/dropdowns/#methods
        e.preventDefault();
        var elements = $("#email-dropdown input:checked:visible");
        if (! elements.length) {
            $("#email-dropdown .validation-error").text(
                "select at least one recipient"
            ).show();
            return false;
        } else {
            $("#email-dropdown .validation-error").hide();
        }
        elements.each(function(){
            var element = $(this);
            var email = element.val();
            var name = element.next().text().trim();
            var html = create_recipient(email,name);
            $("#email-form > .form-group:first-of-type").after(html);
            // hide this row menu, since the address has now been added
            element.closest(".form-group").hide();
        });
        if ( !$("#email-dropdown input:visible").length ) {
            console.log("no inputs to see, therefore hiding dropdown");
            $(".dropdown-toggle").hide(); //, .dropdown-menu
            $("#recipient-autocomplete").attr({placeholder : default_autocomplete_placeholder});
        }
        if ($("#btn-send").hasClass("disabled")) {
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        }
        $(".modal-body .btn, .email-recipient").tooltip();
        // update placeholder text
        //$("#recipient-autoselect").attr({placeholder : "start typing last name..."});
        //return true;
    });
    // don't let the buttons in the dropdown close the menu
    $("#btn-add-recipients + .btn").on("click",function(e) {e.preventDefault()});
});

/**
 * returns HTML for adding an email recipient
 *
 * @param  {string} email
 * @param  {string} name
 * @return {string}
 */
const create_recipient = function(email,name){
    var id, value;
    if (email && name) {
        id = email.toLowerCase().replace("@",".at.");
        value = `${name} &lt;${email}&gt;`;
    } else {
        id = "";
        value = "";
    }
    return `<div class="form-row form-group my-1">
        <label class="col-md-2 text-right" for="${id}">
            <select class="form-control custom-select email-header">
                <option value="to">To</option>
                <option value="cc">Cc</option>
            </select>
        </label>
        <div class="col-md-10">
            <div class="input-group">
                <div class="form-control text-primary">
                    <span title="${email}" class="email-recipient">${name}</span>
                <input class="email-recipient" type="hidden" id="${id}" name="to[]" value="${value}" >
                </div>
                <button class="btn btn-sm btn-primary btn-remove-item border" title="delete this recipient">
                   <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">delete this recipient</span>
               </button>
           </div>
        </div>
    </div>`;
};
