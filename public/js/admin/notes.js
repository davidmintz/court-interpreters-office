$(function(){
    $("#motd, #motw").resizable({
        handles : 'all',
        stop: function (event,ui) {
            var type = $(event.target).attr("id");
            var settings = {[type]: {
                size: {
                    width: `${ui.size.width}px`,
                    height: `${ui.size.height}px`
                }
            }};
            console.warn(settings);
            $.post("/admin/notes/update-settings",settings).fail(fail);
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
            $.post("/admin/notes/update-settings",settings).fail(fail);
        }
    });

    $("#btn-motd, #btn-motw").on("click",function(e){
        e.preventDefault();
        var link = $(e.target);console.log("text is "+link.text());
        var type = e.target.id.indexOf('motd') > -1 ? 'motd':'motw';
        var div = $(`#${type}`);
        var visible = div.is(":visible");
        if (visible) {
            div.slideUp(
                ()=>$.post("/admin/notes/update-settings",{[type]: {visible:0}})
                .then(()=>link.text("show "+type.toUpperCase()))
                .fail(fail)
            );
        } else {
            if (div.find(`.no-${type}`).length) {
                console.log(`now trying to get ${type} for ${div.data().date}`);
                $.get(`/admin/notes/date/${div.data().date}/${type}`)
                .then((res)=>{

                    var key = type.toUpperCase();
                    if (res[key]) {
                        var note = res[key];
                        div.children(".card-body").html(note.content);
                        div.data({id:note.id});
                        var edit_btn = div.find(".card-footer a");
                        edit_btn.attr({ href:
                            `${window.basePath}/admin/notes/edit/${type}/${note.id}/date/${div.data('date')}`
                        });
                    }
                    if (res.assignment_notes) {
                        var html = "";
                        var assignments = res.assignment_notes[type];
                        Object.keys(assignments).forEach(
                            task => {
                                html += `${task}: `;
                                var assigned = assignments[task].assigned;
                                var $default  = assignments[task]["default"];
                                if (assigned.id !== $default.id) {
                                    html += `<span style="text-decoration:line-through">${$default.name}</span> `;
                                }
                                html += `${assigned.name}<br>`;
                            }
                        );
                        div.children(".card-body").prepend(
                            `<div class="border px-2 py-1 mt-0 mb-1">${html}</div>`
                        );
                    }

                    div.slideDown(()=>link.text("hide "+type.toUpperCase()));
                }).fail(fail);
            } else {
                div.slideDown(()=>link.text("hide "+type.toUpperCase()));
            }
            visible = visible ? 0 : 1;
            $.post("/admin/notes/update-settings",{[type]: {visible}}).fail(fail);
        }
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
