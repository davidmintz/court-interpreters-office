
$(function(){
    $('#language-select').on("click",function(event){
        console.log("do shit");
    });
    $('#date-input').datepicker({
        changeMonth: true,
        changeYear: true,
        selectOtherMonths : true,
        showOtherMonths : true
    });
});
