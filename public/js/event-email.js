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

    $("#email-dialog").on("show.bs.modal",function(event){
        // enable tooltips when dialog is shown
        $(`#${this.id} .btn`).tooltip();
        // if the email-select dropdown exists, display it
        $("#email-dialog a.dropdown-toggle").show();
    })
    .on("click",".btn-remove-item",function(event){
        // remove form row (email recipient)
        event.preventDefault();
        $(this).tooltip("hide");
        var div = $(this).closest(".form-row");
        div.slideUp(()=> {
            div.remove();
            /*  -------------  */
            // if no recipients, disable "send" button
            if (! $(`input.email-recipient[name="to[]"]`).length) {
                $("#btn-send").addClass("disabled").attr("disabled");
            }
        });
    })
    .on("click","#btn-cancel",function(){
        // close dialog
        $(".modal-header button.close").trigger("click");
    });
    $("#btn-add-recipients").on("click",function(e){
        e.preventDefault();
        var elements = $("#email-dropdown input:checked");
        if (! elements.length) {
            $("#email-dropdown .validation-error").text(
                "select at least one recipient"
            ).show();
            return false;
        } else {
            $("#email-dropdown .validation-error").hide();
        }
        console.log("looks good, now doing shit...");
        elements.each(function(){
            var element = $(this);
            var email = element.val();
            var name = element.next().text().trim();
            var html = create_recipient(email,name);
            $("#email-form > .form-group:first-of-type").after(html);
            // hide this dropdown menu
            element.closest(".form-group").hide();
        });
        if ( !$("#email-dropdown input:visible").length ) {
            $(".dropdown-toggle, .dropdown-menu").hide();
        }
        if ($("#btn-send").hasClass("disabled")) {
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        }
        $(".modal-body .btn, .email-recipient").tooltip();
        // update placeholder text
        $("#recipient-autoselect").attr({placeholder : "start typing last name..."});
        return true;
    });
    // don't let the "cancel" button in the dropdown submit the form
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
    return `<div class="form-row form-group">
        <label class="col-md-2 text-right" for="${id}">
            <select class="form-control custom-select">
                <option value="to">To</option>
                <option value="cc">Cc</option>
            </select>
        </label>
        <div class="col-md-10">
            <div class="input-group">
                <div class="form-control text-primary">
                    <span title="${email}" class="email-recipient">${name}</span>
                <input class="email-recipient" type="hidden" id="#${id}" name="to[]" value="${value}" >
                </div>
                <button class="btn btn-sm btn-primary btn-remove-item border" title="delete this recipient">
                   <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">delete this recipient</span>
               </button>
           </div>
        </div>
    </div>`;
};
