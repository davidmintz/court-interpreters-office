
$(function(){

    $("#tab-content").on("click","a.request-add",function(){

        var id = $(this).closest("tr").data().id;
        console.log(`id is ${id}`);
        
    });

});
