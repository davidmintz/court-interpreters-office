/**
 * for the admin/court-closings/index viewscript
 *
 */

var $, moment;

$(function(){
    $(".closing-link").on("click",function(event){
        event.preventDefault();
        var href = $(this).attr('href');
        var list = $(this).parent().children("ul");
        $.getJSON(href,function(data){
            var items  = data.map(obj => {
                var el = $("<li>").addClass("list-group-item");
                // to be continued!
                el.text("shit");
                return el;
            });
            list.append(items).show();
        });
    });
});
