 /*
 * public/js/admin/notes/notes-form.js
 */

/*
global $, fail, displayValidationErrors
*/

$(function(){
    var form = $("#notes-form");
    var btn = $("#notes-form button.btn-success");
    btn.on("click",function(e){
        e.preventDefault();
        if ($(`input[name="id"]`).val()) {
            // update
            console.log("do an update");
        } else {
            // create
            console.log("do a create");
        }

    });
});