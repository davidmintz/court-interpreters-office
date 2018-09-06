/**
 * for the admin/court-closings/index viewscript
 *
 */

var $, moment, vm, displayValidationErrors;

$(function(){
    $("a.dropdown-item:contains('court closings')").addClass("active");
    $(".closing-link").on("click",function(event){
        event.preventDefault();
        var link = $(this);
        var href = link.attr('href');
        var list = link.parent().children("ul");
        if (list.is(":visible")) {
            return list.slideUp();
        }
        $.getJSON(href,function(data){
            var items  = data.map(obj => {
                var el = $("<li>").addClass("list-group-item");
                var str = obj.date.date.substring(0,10);
                var date = moment(str,"YYYY-MM-DD").format("dddd MMMM D");
                var link = $("<a>").attr({href : document.location.pathname + "/edit/"+obj.id})
                var text = obj.holiday ? obj.holiday.name : obj.description_other;
                link.text(date);
                el.html(link).append(' - ' +  text);
                return el;
            });
            list.html(items).slideDown();
        });
        $("ul").on("click","li ul li a",function(event){
            event.preventDefault();
            console.log("shit!");
            $("#form-modal .modal-body").load($(this).attr('href') +' form',
            function() {
                vm = create_vm();
                $("#btn-save").on("click",vm.submit);
                $("#form-modal").modal("show");
            });
        });
    });

    $("#btn-add").on("click",function(){
        $("#form-modal .modal-body").load('/admin/court-closings/add form',
        function() {
            vm = create_vm();
            $("#btn-save").on("click",vm.submit);
            $("#form-modal").modal("show");
        });
    });
});
create_vm = function() {
    return new Vue({
        el : "#form-modal",
        data : {
            id : $("input[name=id]").val(),
            date : null,
            holiday : $("#holiday").val(),
            description_other : null
        },
        props : {},
        components : {
            vuejsDatepicker
        },
        methods : {
            date_format : function(){
                return this.date  ?
                moment(this.date).format("YYYY-MM-DD")
                //moment(this.date).format("MM/DD/YYYY")
                : null;
            },
            submit : function() {
                var post = {
                    id : this.id,
                    holiday : this.holiday,
                    description_other : this.description_other,
                    date : this.date_format(),
                    court_closing_csrf :
                        $("input[name='court_closing_csrf']").val()
                }
                var url = '/admin/court-closings/';
                if (this.id) {
                    url += 'edit/'+this.id;
                } else {
                    url += 'add';
                }
                $.post(url,post)
                .then(function(response){
                    console.log("woo hoo");
                    if (response.validation_errors) {
                        return displayValidationErrors(response.validation_errors);
                    }
                    console.log("saved entity");

                });
            }
        },
        computed : {
            title : function(){
                var what;
                if (! this.id) {
                    what = "add new";
                } else {
                    what = "edit"
                }
                return what + " court closing"
            }
        }
    });
};
