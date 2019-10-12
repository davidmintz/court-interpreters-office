 /*
 * public/js/admin/notes/notes-form.js
 */

/*
global $, fail, displayValidationErrors
*/

$(function(){
    // var btn = $("#notes-form button.btn-success");
    $("body .container").on("click","#notes-form button.btn-success",function(e){
        var form = $("#notes-form");
        e.preventDefault();
        var type = $("input[name='type']").val();
        var id = $(`input[name="id"]`).val();
        var url, method;
        if (id) {
            // update
            console.log("doing an update ");
            url = `/admin/notes/update/${type}/${id}`;
            method = 'PUT';
        } else {
            // create
            console.log("do a create");
            url = `/admin/notes/create/${type}`;
            method = 'POST';
        }
        $.ajax({url, method, data : form.serialize()
        }).then((res)=>{
            console.log(res);
        }).fail((res)=>{
            console.log(res);
        });
    });
});
