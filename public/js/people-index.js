$(function(){
    autocomplete_options = {
        source: function(request,response) {
            var params = { term : response.term };
            if ($("#hat").val() !== "") {
                params.hat = $("#hat").val();
            }
            if ($("#active").val() !== "") {
                params.active = $("#active").val();
            }
            console.log(params);
            var data = ["Apple","Banana","Bahooma","Bazinga","Coconut","Dick"];
            return response(data);
        },
        //"/admin/people/autocomplete",
        //source: ["Apple","Banana","Bahooma","Bazinga","Coconut","Dick"],
        minLength: 2,
        select: function( event, ui ) {

        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
    };
    $('#name').autocomplete(autocomplete_options);
    console.warn("shit?");
});
