$(function(){
    $('#judge-filter').on('change',function(){
        var display = $(this).val();
        if (display === "all") {
            $("tr[hidden]").removeAttr("hidden");
        } else if (display == "active") {
            $("tr.judge-active").removeAttr("hidden");
            $("tr.judge-inactive").attr({hidden:true});
        } else {
            $("tr.judge-inactive").removeAttr("hidden");
            $("tr.judge-active").attr({hidden:true});
        }
        $.get(document.location.pathname+`?display=${display}`);
            //.then(function(res){console.log(res)});

    });

    // this works, but doesn't mitigate the problem where when you un-collapse
    // the USMJ when USDJ is already expanded, you have a rude
    // scroll-all-the-way-to-the-bottom effect
    /*
    $('#USMJ').on("show.bs.collapse",function(event){
        //event.preventDefault();
        console.log("USMJ shit was shown!");
        $('#USDJ').collapse("hide");
    });
    */

});
