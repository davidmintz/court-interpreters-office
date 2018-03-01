$(function(){
    $('#judge-filter').on('change',function(){

        if ($(this).val() === "1") {
            $('tr.inactive').show();
        } else {
            $('tr.inactive').hide();
        }
    });

    // this works, but doesn't mitigate the problem where when you un-collapse
    // the USMJ when USDJ is already expanded, you have a rude
    // scroll-all-the-way-to-the-bottom
    /*
    $('#USMJ').on("show.bs.collapse",function(event){
        //event.preventDefault();
        console.log("USMJ shit was shown!");
        $('#USDJ').collapse("hide");
    });
    */

});
