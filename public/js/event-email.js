/** public/js/event-email.js */

const default_autocomplete_placeholder = "start typing last name...";

/* https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/email#Validation */
const pattern = "^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$";

/**
 * returns HTML for adding an email recipient
 *
 * @param  {string} email
 * @param  {string} name
 * @return {string}
 */
const create_recipient = function(email,name, role){
    var id = email.toLowerCase().replace("@",".at.");
    name = name.replace(/"/g,""); // strip quotes
    var value = `${name} &lt;${email}&gt;`;
    if (! role) {
        role = "";
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
                <input class="email-recipient" data-role="${role}" data-recipient-name="${name}" type="hidden" id="${id}" name="to[]" value="${email.toLowerCase()}" >
                </div>
                <button class="btn btn-sm btn-primary btn-remove-item border" title="delete this recipient">
                   <span class="fas fa-times" aria-hidden="true"></span><span class="sr-only">delete this recipient</span>
               </button>
           </div>
        </div>
    </div>`;
};

/**
 * gets short textual description of the event
 *
 * @return {string}
 */
const get_event_description = function(){

    var e = {};
    var fields = ["date","time","event_type","language","location","docket"];
    for (var i = 0; i < fields.length; i++) {
        var div = $(`.${fields[i]}`);
        if (div.children("ins").length) {
            e[fields[i]] = div.children("ins").text().trim();
        } else if (div.children("del").length) {
            e[fields[i]] = "";
        } else {
            e[fields[i]] =  div.text().trim();
        }
    }
    e.category = $("div.event-details").data("event_category");
    var text = e.date;
    if (e.time) {
        text += ` ${e.time}`.replace(/ (a|p)m/,"$1");
    }
    text += `, ${e.language} ${e.event_type}`
    if (e.category !== "in" && location) {
        text += `, ${e.location}`;
    }
    if (e.docket) {
        e.docket = e.docket.replace(/^(\d{2})(\d{2})(.+)/,"$2$3");
        text += ` (${e.docket})`
    }

    return text;

};

/**
 * gets object containing event details for composing email
 *
 * @return {object}
 */
const get_event_details = function()
{
    var data = {};
    var fields = [
        "date","time","language","judge","event_type","location",
        "docket","defendants","interpreters"
    ];
    for (var i = 0; i < fields.length; i++) {
        var obj = $(`.${fields[i]}`);
        if (!obj.length) {
            console.warn("!! dude, no ."+fields[i]);
        } else {
            //var html = obj.html().trim();
            data[fields[i]] = obj.html().trim();
        }
    }
    return data;
};
/*
date 	23-Apr-2019
time 	10:00 am
language 	Russian
event type 	pretrial conference
location 	11A
docket 	2018-CR-0321
comments 	this is a Russian request created by data loader
judge 	Daniels, George, USDJ
defendants 	Snyertzski, Boris
 */
/**
 * determines whether to suggest sending email notification about
 * newly updated event
 * @return {boolean}
 */
const should_suggest_email = function()
{
    if ( ! $('span.interpreter').length || ! $("ins, del").length ) {
        return false;
    }
    var date_str = $(".event-details").data().event_datetime;
    var event_datetime =  moment(date_str,"YYYY-MM-DD HH:mm");
    var minutes_from_now = moment().add(10,'minutes');
    if (event_datetime.isBefore(minutes_from_now)) {
        return false;
    }
    var email_flag = false;
    var noteworthy = ["date","time","event_type","interpreters","location","cancellation"];
    var n = noteworthy.length;
    for (var i = 0; i < n; i++) {
        var div = $(`.${noteworthy[i]}`);
        var updated = div.find("ins").length + div.find("del").length;
        if (updated) {
            email_flag = true;
            break;
        }
    }
    return email_flag;
};
/**
 * Displays a message encouraging user to send email.
 * @return {void}
 */
const display_email_suggestion = function() {
    var who = "interpreter";
    if ($("span.interpreter").length > 1) {
        who += "s";
    }
    var div = $(`<div style="max-width:400px">`).addClass("alert alert-warning text-center border mx-auto").html(
         `<a id="link-email-suggest" href="#">Email notification to the ${who}?</a>`
    );
    div.prepend(`<button type="button" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></button>`);
    div.insertBefore($(".event-details"));
    /** @todo consider this? */
    var interpreter_update_notice = true;
    // var interp_modifications =  $("div.interpreters span").children("ins, del").length;
    // if (interp_modifications &&
    //     interp_modifications ===  $("ins, del").length - $("div.last-modified").children("ins, del").length
    // ) {
    //     //console.warn("looks like more than just the interpreters were updated");
    //     interpreter_update_notice = false;
    //     $("#message-subject").val("");
    // } else {
         $("#message-subject").val("assignment update: "+get_event_description());
    // }

    $("#link-email-suggest").on("click",function(event){
        event.preventDefault();
        $("#btn-email").trigger("click",{interpreter_update_notice});
    });
};

/**
 * assembles and submits email, and handles response.
 *
 * @param  {object} event
 * @return {void}
 */
const send_email = function(event){
    console.log("show time!");
    var message = {to: [], cc: [] };
    if ($("#include-details").prop("checked")) {
        console.log("doing event details...");
        message.event_details = get_event_details();
    }
    $("input.email-recipient").each(function(){
        var input = $(this);
        var email = input.val();
        var recipient = {email};
        if (input.data("recipient-name")) {
            recipient.name = input.data("recipient-name");
        }
        recipient.role = input.data("role") || "";
        if (input.attr("name") === "to[]") {
            message.to.push(recipient);
        } else {
            message.cc.push(recipient);
        }
    });
    message.subject = $("#message-subject").val().trim();
    message.body = $("#message-body").val().trim();
    message.template_hint = $("#template").val()||$("#subject-dropdown").data("template_hint");

    //var csrf = 'shit';// testing
    var csrf = $("[data-csrf]").data("csrf");
    var url = "/admin/email/event";
    $.post(url,{message, csrf}).done((response)=> {
        if (response.status !== "success") {
            if (response.validation_errors) {
                $("#error-message ul").remove();
                if (response.validation_errors.csrf) {
                    $("#error-message p").html(
                        `Sorry, this action has failed because your security token was invalid or timed out.`
                    ).parent().show();
                } else {
                    $("#error-message p").html( `Sorry, we can't process this submission because`);
                    var ul = `<ul class="ml-3">`;
                    for (var key in response.validation_errors) {
                        var content;
                        if (Array.isArray(response.validation_errors[key])) {
                            if (0 === response.validation_errors[key].length) {
                                continue;
                            }
                            content = response.validation_errors[key].join("</li><li>");
                        } else {
                            content = response.validation_errors[key];
                        }
                        ul += `<li>${content}</li>`;
                    }
                    ul += "</ul>";
                    $("#error-message p").after(ul).parent().show();
                }
            }
        } else {
            $("#error-message").hide();
            console.log("it worked");
        }
    }).fail(function(response){console.warn("shit")});
}

$(function(){
    // rig it
    // console.warn("faking time-update for test purposes...");
    // $(".time").html(`<del>2:30 pm</del> <ins>4:00 pm</ins>`);
    console.log(`email flag? ${should_suggest_email()}`);
    if (should_suggest_email()) {
        display_email_suggestion();
    }
    $("[data-toggle=tooltip]").tooltip();
    $("div.popover").css({zIndex:1500});
    const boilerplate_popover  = $(`label[for=template] a[data-toggle="popover"]`);
    /* popover with help info for the boilerplate/template control */
    boilerplate_popover.popover({
        html : true,
        trigger: "click",
        container : "#email-form",
        sanitize: false,
        content : `<button type="button" class="close" data-dismiss="modal" aria-label="close">
                    <span aria-hidden="true">×</span></button> which <a href="/admin/email/templates" target="_blank">template</a> to use
        for verbiage preceding event details.`
    });
    $("#email-form").on("click",".popover-body button.close",function(e){
        e.stopPropagation();
        boilerplate_popover.popover("hide");
    });
    var btn_manual_add = $("#btn-add-recipient");
    var description = get_event_description();
    $("#email-modal-label").append(` re: ${description}`);

    /* the "event-details" checkbox */
    $("#include-details").on("change",function(e){
        var checked = $(this).prop("checked");
        if (! checked) {
            $("#template").attr({disabled:"disabled"});
        } else {
            $("#template").removeAttr("disabled");
        }
    });
    /* listener for event/view "email" button and for the "+" adjacent to autocomplete input */
    $("#btn-email, #btn-add-recipient").on("click",function(e,params){
        e.preventDefault();
        // if they clicked the 'notify the interpreter...' link
        if (params && params.interpreter_update_notice) {
            $("input[data-role='interpreter']").each(function(){
                var input = $(this);
                var email = input.val().toLowerCase();
                if ($(`input.email-recipient[value="${email}"]`).length) {
                    return;
                }
                var name = input.next("label").text().trim();
                var html = create_recipient(email,name,"interpreter");
                $("#email-form .email-subject").before(html);
                input.attr("disabled","disabled");
                if (! $("#email-dropdown input").not(":disabled").length)
                {
                    console.log("hiding dropdown for fux sake?");
                    $(".dropdown-toggle-recipients").hide();
                    $("#recipient-autocomplete").attr({placeholder : default_autocomplete_placeholder});
                }
            });
            $("#template").val("update");
        }
        if ($("input[name='to[]']").length && $("#btn-send").hasClass("disabled")) {
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        } else { console.warn("shit?");}
    });
    /* initialize autocompletion, etc for email recipient in dialog */
    $("#email-dialog").on("shown.bs.modal",function(event){
        $("#recipient-autocomplete").autocomplete({
            source: function(request,response) {
                var params = { term : request.term, active : 1, value_column : "email" };
                $(this).data({searching:true})
                // to do: error handling
                $.get("/admin/people/autocomplete",params,"json").then(
                    function(data){
                        $(this).data({searching:false})
                        return response(data);}
                );
            },
            minLength: 2,
            select: function( event, ui ) {
                event.preventDefault();
                var email = ui.item.value.toLowerCase();
                if ($(`.form-control input[value="${email}"]`).length) {
                    console.log(`duplicate: ${email}`);
                    /** @todo some error message or other feedback */
                    return;
                }

                var role = ui.item.hat.indexOf("interpreter") > -1 ?
                    "interpreter" : "submitter";
                // flip the last/first names
                var n = ui.item.label.lastIndexOf(", ");
                var name = `${ui.item.label.substring(n+2)} ${ui.item.label.substring(0,n)}`;
                //console.warn(ui.item.label);
                var html = create_recipient(email, name ,role);
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
            // try to detect whether a lookup is in progress
            // and if not, validate the input
            if ($(this).data("searching") || $("ul.ui-autocomplete:visible").length) {
                return;
            }
            var input = $(this).val().trim();
            var name, email, valid;
            if (input.match(pattern)) {
                // it looks plausible(ish)
                email = input;
                valid = true;
            } else {
                var parts = input.split(/\s+/);
                if (parts.length > 1) {
                    email = parts.pop().replace(/[<>]/g,"");
                    if (email.match(pattern)) {
                        name = parts.join(" ");
                        valid = true;
                    }
                }
            }
            if (valid) {
                btn_manual_add.removeClass("btn-secondary")
                    .removeClass("disabled")
                    .addClass("btn-primary");
            } else {
                if (btn_manual_add.hasClass("btn-primary")) {
                    btn_manual_add.removeClass("btn-primary")
                        .addClass("btn-secondary disabled");
                }
            }
        });

        $("#recipient-autocomplete").autocomplete("instance")._renderItem =
         function(ul, item) {
            // return $("<li>").append(item.label).appendTo(ul);
            return $( "<li>" )
                .attr( "data-hat", item.hat ).attr("title",item.hat)
    			.append( $( "<div>" ).text( item.label ) )
                .appendTo( ul );
         };
         // the button they can click to add a recipient whose email and name
         // they have typed manually
        btn_manual_add.on("click",function(){
            if ($(this).hasClass("disabled")) {
                return;
            }
            var email, name = "", value = $("#recipient-autocomplete").val().trim();
            var parts = value.split(/\s+/);
            if (parts.length) {
                email = parts.pop().replace(/[<>]/g,"");
                if (parts.length) {
                    name = parts.join(" ");
                }
            }
            console.warn(`valid? ${name || "[no-name]"} <${email}>`);
            var html = create_recipient(email,name || "");
            $(".email-subject").before(html);
            $(this).tooltip("hide");
            $("#recipient-autocomplete").val("");
            btn_manual_add.removeClass("btn-primary")
                .addClass("btn-secondary disabled");
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        });
    });

    /* enable tooltips when dialog is shown */
    $("#email-dialog").on("show.bs.modal",function(event){
        $(`#${this.id} .btn`).tooltip();
    })
    /* remove form row (email recipient) when they click "x" */
    .on("click",".btn-remove-item",function(event){
        event.preventDefault();
        $(this).tooltip("hide");
        var input = $(this).prev(".form-control").children("input");
        var email = input.val();
        var dropdown_item = $(`#email-dropdown input[value="${email}"]`);
        if (dropdown_item.length && dropdown_item.is(":disabled")) {
            dropdown_item.removeAttr("disabled");
            console.log(`re-enabled ${email}`);
            var dropdown_menu = $(".dropdown-toggle-recipients");
            if (dropdown_menu.is(":hidden")) {
                console.log("re-displaying recipient-dropdown");
                dropdown_menu.show();
                $("#recipient-autocomplete").attr({
                    placeholder : "start typing last name, or use the dropdown"
                });
            }
        }
        var div = $(this).closest(".form-row");
        div.slideUp(()=> {
            div.remove();
            // if no recipients, disable "send" button
            if (! $(`input.email-recipient[name="to[]"]`).length) {
                $("#btn-send").addClass("disabled").attr("disabled");
            }
            if (0 === $("input.email-recipient[data-role=submitter]").length) {
                // subject should not be "your request"
                if ( "your request" === $("#subject-dropdown").data("template_hint")) {
                    $("#subject-dropdown").data({template_hint:""});
                    $("#message-subject").val("");
                }
            }
        });
    })
    /* toggle To|Cc email header */
    .on("change", "select.email-header",function(){
        var input = $(this).parent().next().find("input.email-recipient");
        var name = input.attr("name") ===  "to[]" ? "cc[]" : "to[]";
        input.attr({name});
        if (! $(`input.email-recipient[name="to[]"]`).length) {
            $("#btn-send").addClass("disabled").attr("disabled");
        } else {
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        }
    })
    /* close email dialog */
    .on("click","#btn-cancel",function(){
        $(".modal-header button.close").trigger("click");
    });
    $("#btn-add-recipients").on("click",function(e){
        // https://getbootstrap.com/docs/4.3/components/dropdowns/#methods
        e.preventDefault();
        $(this).tooltip("hide");
        var elements = $("#email-dropdown input:checked").not(":disabled");
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
            var email = element.val().toLowerCase();
            var name = element.next().text().trim();
            //console.log(element.data());
            if (! $(`.form-control input[value="${email}"]`).length) {
                var html = create_recipient(email,name,element.data("role"));
                $("#email-form .email-subject").before(html);
            }
            // disable this element, since the address has now been added
            element.attr("disabled","disabled");
        });
        if ( !$("#email-dropdown input").not(":disabled").length ) {
            console.log("no inputs to see, therefore hiding dropdown");
            $(".dropdown-toggle-recipients").hide(); //, .dropdown-menu
            $("#recipient-autocomplete").attr({placeholder : default_autocomplete_placeholder});
        }
        if ($("#btn-send").hasClass("disabled")) {
            $("#btn-send").removeClass("disabled").removeAttr("disabled");
        }
        $(".modal-body .btn, .email-recipient").tooltip();
    });
    /* don't let the buttons in the dropdown close the menu */
    $("#btn-add-recipients + .btn").on("click",function(e) {e.preventDefault()});
    /* listener for email-subject dropdown */
    $("#subject-dropdown .dropdown-item").on("click",function(event){
        var template_hint = $(this).data("subject");
        $("#subject-dropdown").data({template_hint});
        $(this).tooltip("hide");
        var subject_line;
        $("#template").val(template_hint);
        switch (template_hint) {
            case "your request":
            var el = $("#email-dropdown input[data-role=submitter]");
            var email = el.val().toLowerCase();
            if (0 === $(`input.email-recipient[value="${email}"]`).length) {
                console.log(`adding ${email}...`);
                var name = el.next("label").text().trim();
                var markup = create_recipient(email, name, "submitter");
                $("#email-form .email-subject").before(markup);
                /** enable the send button ! */
                $("#btn-send").removeClass("disabled").removeAttr("disabled");
            }
            subject_line = "your request for ";
            break;
            case "available":
            subject_line = "interpreter needed: ";
            break;
            case "update":
            subject_line = "assignment update: ";
            break;
            case "confirmation":
            subject_line = "assignment confirmed: ";
            break;
            case "cancellation":
            subject_line = "assignment cancelled: ";
            break;
        }
        $("#message-subject").val(subject_line + description);
    });

    $("#btn-send").on("click",send_email);

});
