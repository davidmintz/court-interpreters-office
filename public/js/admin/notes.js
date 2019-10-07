$(function(){
    $("#motd, #motw").resizable({
        //handles : 'all',
        stop: function (event,ui) {
            var type = $(event.target).attr("id");
            var settings = {[type]: {
                size: {
                    width: `${ui.size.width}px`,
                    height: `${ui.size.height}px`
                }
            }};
            console.warn(settings);
            $.post("/admin/notes/update-settings",settings);
        }
    });
    $("#motd, #motw").draggable({
        stop: function (event,ui) {
            //var settings = {};
            var settings = {[event.target.id] : {
                position: {
                    top: `${ui.position.top}px`,
                    left: `${ui.position.left}px`
                }
            }};
            $.post("/admin/notes/update-settings",settings);
        }
    });

    $("#btn-motd, #btn-motw").on("click",function(e){
        e.preventDefault();
        var type = e.target.id.indexOf('motd') > -1 ? 'motd':'motw';
        var div = $(`#${type}`);
        div.toggle();
        var visible = div.is(":visible");
        $(`#btn-${type}`).text(`${visible ? "hide":"show"} ${type.toUpperCase()}`);
        if (div.find(`.no-${type}`).length){
            console.log(`now try to get ${type} for ${div.data().date}`);
            $.get(`/admin/notes/date/${div.data().date}/${type}`).then((res)=>{
                //div.children(".card-body").replaceWith(res.MOTD.content);
            });
        }
        $.post("/admin/notes/update-settings",{[type]: {visible: visible ? 1 : 0}})
    });

    $(".card-header button[data-hide]").on("click",function(){
        var what = $(this).closest(".card").attr("id");
        $(`#btn-${what}`).trigger("click");
    });

    var motd_visible = $("#motd").is(":visible");
    $("#btn-motd").text(`${motd_visible ? "hide":"show"} MOTD`);
    var motw_visible = $("#motw").is(":visible");
    $("#btn-motw").text(`${motw_visible ? "hide":"show"} MOTW`);


});
