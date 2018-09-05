/**
 * for the admin/court-closings/index viewscript
 *
 */

var $, moment;

$(function(){
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
                var text = obj.holiday ? obj.holiday.name : obj.description_other;
                el.html(date + ' - ' +  text);
                return el;
            });
            list.html(items).slideDown();
        });
    });
});
