 /*
 * public/js/admin/notes/notes-form.js
 */

/*
global $, fail, displayValidationErrors, moment
*/
/*

onSelect
Type: Function( String dateText, Object inst )
Default: null
Called when the datepicker is selected. The function receives the selected date as text and the datepicker instance as parameters.
'this' refers to the associated input field.
*/
$(function(){
    dp_defaults = {
        dateFormat:"yy-mm-dd",
        showOtherMonths : true,
        changeMonth : true,
        changeYear : true,
        onSelect :  function(dateText,instance) {
            var type = instance.id.substring(9);
            var url = `/admin/notes/date/${dateText}/${type}`;
            var content_div = $(`#${type}-content`);
            var key = type.toUpperCase();
            var form_url;
            $.getJSON(url)
            .then(function(res){
                if (res[key]) {
                    content_div
                        .html(res[key].content)
                        .prev("h5").text(res[key].date || `week of ${res[key].week_of}`);
                        //https://office.localhost/admin/notes/edit/motd/4581/date/2019-10-23
                    form_url = `/admin/notes/edit/${type}/${res[key].id}/date/${dateText}`;
                } else {  // fuck, so much effort!
                    var h5, date = moment(dateText,'YYYY-MM-DD');
                    if (key === "MOTW") {
                        var dow = parseInt(date.format("E"));
                        if (dow !== 1) {
                            date.subtract(dow - 1, "days");
                        }
                        h5 = `week of ${date.format("dddd DD-MMMM-YYYY")}`;
                    } else {
                        h5 = date.format("dddd DD-MMMM-YYYY");
                    }
                    content_div
                        .html(`<p class="font-italic no-note">no ${key} for this date</p>`)
                        .prev("h5").text(h5);
                    form_url = `/admin/notes/create/${type}/${dateText}`;
                }
                console.log(form_url+ " is where button should point");
            });
        }
    };
    $("#calendar-motd").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motd").data("date")})
    );
    $("#calendar-motw").datepicker(
        Object.assign(dp_defaults,{defaultDate : $("#calendar-motw").data("date")})
    );
    /** handler for loading edit|create form */
    $("#tab-content-notes").on("click","#btn-editor-motd, #btn-editor-motw",function(e){
        e.preventDefault();
        console.warn("do shit: load form for "+this.href);
    });
    /** form submission handler for edit|create form */
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
        console.log("if no MOT[DW], try harder");
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
