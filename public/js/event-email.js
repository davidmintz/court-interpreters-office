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

    $("#btn-email").on("click",function(e){e.preventDefault();});

    $("#email-dialog").on("show.bs.modal",function(event){
        $(`#${this.id} .btn`).tooltip();
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
            // hide this
            element.closest(".form-group").hide();
        });
        if ( !$("#email-dropdown input:visible").length ) {
            $(".dropdown-toggle, .dropdown-menu").hide();
        }
        if ($("#btn-send").hasClass("disabled")) {
            $("#btn-send").removeClass("disabled");
        }
        $(".modal-body .btn").tooltip();
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
    if (email) {
        id = "#" + email.toLowerCase().replace("@",".at.");
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
                <input type="text" id="#${id}" name="to[]" class="form-control" value="${value}" placeholder="type last name">
                <button class="btn btn-sm btn-primary btn-remove-item border" title="delete recipient">
                   <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">delete recipient</span>
               </button>
               <button class="btn btn-sm btn-primary btn-add-recipient border" title="add another recipient">
                  <span class="fas fa-plus" aria-hidden="true"></span><span class="sr-only">add another recipient</span>
              </button>
           </div>
        </div>
    </div>`;
};
