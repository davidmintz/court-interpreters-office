 /*
 * public/js/admin/notes/notes-form.js
 */

/*
global $, fail, displayValidationErrors
*/

$(function(){
    var motd_default_date =
    $("#calendar-motd").datepicker({
        dateFormat:"yy-mm-dd",
        defaultDate : $("#calendar-motd").data("date")
    });
    $("#calendar-motw").datepicker({
        dateFormat:"yy-mm-dd",
        defaultDate : $("#calendar-motw").data("date")
    });
    $("#tab-content-notes").on("click","#btn-edit-motd",function(e){
        e.preventDefault();
        console.warn("do shit: "+this.href);
    });
    // var btn = $("#notes-form button.btn-success");
    $("#tab-content-notes").on("click","#notes-form button.btn-success",function(e){
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
    $("#tabs-notes .nav-link").on("click",function(e){
        e.preventDefault();
        if ($(this).hasClass("active")) {
           console.debug("this one is active already");
           return;
        }
        /**
         * to do here:
         * figure out which tab we're dealing with
         * check whether there is a MOT[DW] loaded
         * if not, try to fetch it via xhr
         * then call show
         */
        $(this).tab("show");
    });

});
