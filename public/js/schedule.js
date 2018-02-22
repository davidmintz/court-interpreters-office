
$(function(){
    $('#language-dropdown .dropdown-menu a').on("click",function(event){
        event.preventDefault();
        $('#language-filter').text($(this).text());
    });

});
