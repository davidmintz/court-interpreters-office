$(function(){
    $("form .btn-success").on("click",function(){
        var form = $(this).closest("form");
        $.post(form.attr("action"),form.serialize())
        .then((res)=>{console.log(res)});
    });
});
