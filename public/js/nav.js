/** event handlers for main nav quick-search feature*/
var formatDocketElement;
$(function(){
    $("#nav-search").on("shown.bs.dropdown",()=>input.focus());
    var btn = $(`#btn-docket-search`);
    btn.on("click",function(e){
        e.preventDefault();
        if (!input.data().valid) {
            e.stopPropagation();
        } else {
            document.location = `${btn.attr("href")}/${input.val()}`;
        }
    });
    var input = $(`.nav-item input[name="docket"]`);
    input.on("change",(e,params)=>{
        formatDocketElement(e);
        var submit = params && params.submit;
        if (input.data().valid && submit) {
            btn.trigger("click");
        }
    })
    .on("keypress",(e)=> {
        if (e.which === 13) {
            input.trigger("change",{submit:true});
        }
    });
});
