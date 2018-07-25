$(function(){

    /** toggle visibility of judge control depending on the hat */
    $("#hat").on("change",function(){
        if (! $(this).val()) {
            $("#judge-div").hide();
            return;
        }

        var hat = $(this).children(":selected");
        if (hat.data().is_judges_staff) {
            $("#judge-div").slideDown();
        } else {
            $("#judge-div").slideUp();
        }
    });
    /** sort of a template for the judge widget */
    var judge_tmpl = $("<li>").addClass("list-group-item py-1").append(
        "<button type=\"button\" title=\"click to remove this judge\" class=\"btn "
        + "btn-warning btn-sm float-right remove-div\">X</button>"
    );

    /** append a judge */
    var appendJudge = function(event) {
        event.preventDefault();
        var id = $("#judge-select").val();
        if (! id) { return ; }
        var selector = "li input[value="+id+"]";
        if ($(selector).length) {
            return;
        }
        var element = judge_tmpl.clone();
        var name = $("#judge-select option:selected").text();
        element.prepend(name).prepend(
            $("<input>")
                .attr({type:"hidden",name:"user[judges][]",value:id}))
            .appendTo($("#judges"));
        $("#judge-select").val("");
        $("#judge-div .validation-error").hide();
    };
    /** assign handler */
    $("#btn-add-judge").on("click",appendJudge);

    /** remove a judge */
    $("#judges").on("click",".remove-div",function(){
        $(this).parent().remove();
    });


});
