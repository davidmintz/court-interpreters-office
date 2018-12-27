var $;

$(function(){
    var form = $("#config-form");
    var btn_restore_defaults = $("#restore-defaults");
    var status = $("#status");
    $("#save").on("click",function(event){

        var data = form.serialize();
        $.post(form.attr("action"),data)
        .done((response)=>{
            status.text("Your custom settings have been saved.").parent().show();
            if (response.created_custom_settings || response.updated_custom_settings) {
                // show the restore-defaults button
                btn_restore_defaults.show();
            } else {
                status.parent().hide();
            }
        })
        .fail(fail);
    });

    btn_restore_defaults.on("click",function(event){
        $.post(form.attr("action"),{'restore-defaults':1})
        .done((response)=>{
            // reload the form
            $("#config-form table")
            .load(`${document.location.href} #config-form table tbody`,()=>{
                // if the defaults have been restored, don't show this button
                btn_restore_defaults.hide();
                status.text("The default settings have been restored.")
                .parent().show();
            })
        })
        .fail(fail);

    });
});
