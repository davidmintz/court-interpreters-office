/* global fail */
var $;

const handle_write_error = function(response){
    document.getElementById("top").scrollIntoView({behavior: "smooth"});
    if (! response.responseJSON || ! response.responseJSON.error) {        
        fail(response);
    } else {        
        $("#error-message").text(response.responseJSON.error);
        $("#error-div").show();
    }    
};

$(function () {
    var form = $("#config-form");
    var btn_restore_defaults = $("#restore-defaults");
    var status = $("#status");
    $("#save").on("click", function () {

        var data = form.serialize();
        $.post(form.attr("action"), data)
            .done((response) => {
                status.text("Your custom settings have been saved.").parent().show();
                if (response.created_custom_settings || response.updated_custom_settings) {
                    // show the restore-defaults button
                    btn_restore_defaults.show();
                } else {
                    status.parent().hide();
                }
            })
            .fail(handle_write_error);
    });

    btn_restore_defaults.on("click", function () {
        $.post(form.attr("action"), { "restore-defaults": 1 })
            .done(function () {
                // reload the form
                $("#config-form table")
                    .load(`${document.location.href} #config-form table tbody`, () => {
                        // if the defaults have been restored, don't show this button
                        btn_restore_defaults.hide();
                        status.text("The default settings have been restored.")
                            .parent().show();
                    });
            })
            .fail(handle_write_error);

    });
});
