/**
 * for the admin/court-closings/index viewscript
 *
 */

var $, moment, vm;

$(function(){
    $("a.dropdown-item:contains('court closings')").addClass("active");
    $(".closing-link").on("click",function(event){
        event.preventDefault();
        var link = $(this);
        var href = link.attr('href');
        var list = link.parent().children("ul");
        if (list.is(":visible")) {
            return list.slideUp();
        }
        $.getJSON(href,function(data){
            var items  = data.map(obj => {
                var el = $("<li>").addClass("list-group-item");
                var str = obj.date.date.substring(0,10);
                var date = moment(str,"YYYY-MM-DD").format("dddd MMMM D");
                var link = $("<a>").attr({href : document.location.pathname + "/edit/"+obj.id})
                var text = obj.holiday ? obj.holiday.name : obj.description_other;
                link.text(date);
                el.html(link).append(' - ' +  text);
                return el;
            });
            list.html(items).slideDown();
        });
        $("ul").on("click","li ul li a",function(event){
            event.preventDefault();
            console.log("shit!");
        });
    });

    $("#btn-add").on("click",function(){
        $("#form-modal .modal-body").load(
            '/admin/court-closings/add form',
            function(){

                vm = new Vue({
                    el : "#form-modal",
                    data : {
                        id : null,
                        date : null,
                        holiday : null, //$("#holiday").val(),
                        description_other : null
                    },
                    components : {
                        vuejsDatepicker
                    },
                    methods : {
                        date_format : function(){
                            return this.date  ?
                            moment(this.date).format("YYYY-MM-DD")
                            : null;
                        },
                        submit : function() {
                            var post = {
                                id : this.id,
                                holiday : this.holiday !== "other" ?
                                    this.holiday : null,
                                description_other :
                                    this.holiday === "other" ?
                                    this.description_other : null,
                                date : this.date_format()
                            }
                            var url = '/admin/court-closings/';
                            if (this.id) {
                                url += 'edit/'+this.id;
                            } else {
                                url += 'add';
                            }
                            $.post(url,post)
                            .then(function(response){
                                console.log("woo hoo");
                            });
                        }
                    },
                    computed : {
                        title : function(){
                            var what;
                            if (! this.id) {
                                what = "add new";
                            } else {
                                what = "edit"
                            }
                            return what + " court closing"
                        }
                    }
                });
                $("#form-modal").modal("show");
            });

    });
});
/*
<select class="form-control form-select" name="holiday" v-model="holiday">
    <option value=""> -- </option>
    <option value="1">Christmas</option>
    <option value="2">New Years</option>
    <option value="3">Martin Luther King</option>
    <option value="-1">other...</option>
</select>
<input v-bind:style="{display: holiday == -1 ? 'inline':'none'}" placeholder="description" name="description_other" v-model="description_other">*/
