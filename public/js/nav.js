var formatDocketElement;
$(function(){
    var input = $(`.nav-item input[name="docket"]`);
    input.on("change",formatDocketElement);
    var btn = $(`#btn-docket-search`);
    btn.on("click",function(e){
        e.preventDefault();
        if (!input.data().valid) {
            e.stopPropagation();
        } else {
            document.location = `${btn.attr("href")}/${input.val()}`;            
        }
    });
});
