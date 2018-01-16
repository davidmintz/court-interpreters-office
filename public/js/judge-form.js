$(document).ready(function(){ 
    // this whole thing needs cleaning up
    $('#add-location').on('show.bs.modal',function(event){
        console.log("show event fired");
        // event.relatedTarget is the button they clicked, FYI
        $('#add-location > div > div > div.modal-body').load(
            '/admin/locations/add?form_context=judges form',
            function(){
                $('.modal-header .modal-title').text("add a courthouse/courtroom")
                // use the modal's buttons instead
                $('#add-location input[name="submit"]').remove();
        });
    });
    // if there is no courthouse selected, disable the courtroom control
    var courthouseSelect = $('#courthouse');
    var courtroomSelect = $('#courtroom');
    if (! courthouseSelect.val()) {
        courtroomSelect.val('').attr({disabled : "disabled"});
    }
    courthouseSelect.on('change',function(){
        var parent_id = courthouseSelect.val();
        if (! parent_id) {
            return courtroomSelect.val('').attr({disabled : "disabled"});
        }
        var data = $.getJSON('/admin/locations/courtrooms/'+parent_id, null,
            function(data){

                var options = [], i = -1;
                $.each(data,function(){
                    options[++i] = $('<option/>').attr({label:this.name, value: this.id }).text(this.name);
                });            
                if (courtroomSelect.children().length > 1) {
                    // might want to cache these later
                    var discard = courtroomSelect.children().slice(1).remove();
                }
                courtroomSelect.append(options);
            }
         );
    }); 
    $('#courthouse, #courtroom').on('change',function(event){
        var defaultLocation = $('#defaultLocation');
        var selectElement = $(event.target);
        // if the courtroom changed
        if ('courtroom' === selectElement.attr("id")) {
            defaultLocation.val(selectElement.val());
        } else {
            // the courthouse is what changed. if there is no courtroom,
            // the courthouse value becomes the default location value
            if (! courtroomSelect.val()) {
                defaultLocation.val(selectElement.val());
            }
            if (courthouseSelect.val()) {
                courtroomSelect.prop('disabled',false);
            }
        }
        console.debug("set defaultLocation value to: "+defaultLocation.val());
    });
    $('#add-location').on("click",'#btn-add-location-submit', function(){

        $.post('/admin/locations/add?form_context=judges',$('#add-location form').serialize(),
        function(response){

            if (! response.valid) {
               return displayValidationErrors(response.validationErrors);
            }
            $('#add-location').modal("hide");
            $("#defaultLocation").val(response.entity.id);
            $('#courthouse').prop('disabled',false);
            entity = response.entity;

            var targetElement = $('#'+entity.type);
            $("<option>")
                .attr({label: entity.name, value: entity.id})
                .text(entity.name)
                .appendTo(targetElement);
            targetElement.val(response.entity.id);
            if (entity.parent_location_id) {
                $('#courthouse').val(entity.parent_location_id);
            }
        });
    });
});
// borrowed from ourself
__displayValidationErrors = function(validationErrors) {
$('.validation-error').empty();
for (var field in validationErrors) {
    //console.log("examining field "+field);
    for (var key in validationErrors[field]) {
        // console.log("examining key "+key);
        var message = validationErrors[field][key];
        var element = $('#' +field);
        var errorDiv = $('#error_'+field);
        if (! errorDiv.length) { errorDiv = null;}
        if (! element.length) { console.log("is there no element #"+field+ " ?");
            // look for an existing div by id
            if ($('#error_'+field).length) {
                $('#error_'+field).html(message);
            } else {
                console.warn("no element with id "+field + ", and nowhere to put message "+message);
            }
        } else {
            errorDiv = errorDiv || element.next('.validation-error');
            if (! errorDiv.length) {
                errorDiv = $('<div/>')
                        .addClass('alert alert-warning validation-error')
                        .attr({id:'error_'+field})
                .insertAfter(element);
            }
            errorDiv.html(message).show();
        }
        break;
    }
} 
};
