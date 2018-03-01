$(function(){
    $('#judge-filter').on('change',function(){
        console.warn("change! " + $(this).val());
        if ($(this).val() === "1") {
            $('tr.inactive').show();
        } else {
            $('tr.inactive').hide();
        }
    });

    // this works, but doesn't mitigate the problem where when you un-collapse
    // the USMJ you have a rude scroll-all-the-way-to-the-bottom
    /*
    $('#USMJ').on("show.bs.collapse",function(event){
        //event.preventDefault();
        console.log("USMJ shit was shown!");
        $('#USDJ').collapse("hide");
    });
    */
    $('#USMJ').on("click",function(e){e.preventDefault()})
});
