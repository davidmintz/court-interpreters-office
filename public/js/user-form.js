/**
 * public/js/user-form.js
 */
$(document).ready(function(){
    // dynamically re-populate role element depending on hat element value
    var hatElement = $('#hat');
    var roleElement = $('#role');
    hatElement.on('change',function(){
        var hat_id = hatElement.val();
        if (! hat_id) {
            return;
        }
        $.getJSON('/admin/users/role-options/'+hat_id,{}, function(data){
            console.log(data);
            var options = data.map(function(item){
                return $('<option>').val(item.value).text(item.label)
                    .data({type: item.type});
            });
            roleElement.children().slice(1).remove();
            roleElement.append(options);
            if (options.length === 1) {
                roleElement.children(":last").attr({selected:"selected"});
            }
        });
    });    
});
