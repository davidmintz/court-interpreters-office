$(function(){
        var selector = document.location.pathname.indexOf("search") > -1 ?
            "#results":"#content";
        const content = $(selector);
        // on dropdown menu click, conditionally enable disabled items
        content.on("click","td.dropdown > a",function(event){
            event.preventDefault();
            var editable = $(this).closest("tr").data().editable
            var disabled_items = $(`#${$(this).attr('id')}`).next(".dropdown-menu").children("a.disabled");
            if (editable) {
                disabled_items.removeClass("disabled").children("span").removeClass("text-muted");
            } else {
                disabled_items.attr({title:"this action is no longer available"})
                    .on("click", (e) => e.preventDefault()
                );
            }
        });
        content.on("click","a.request-cancel",function(event){
            event.preventDefault();
            var tr = $(this).closest("tr");
            var date = tr.children(".date").text();
            var time = tr.children(".time").text();
            var language = tr.children(".language").text();
            var type =  tr.children(".event-type").text();
            var id = tr.data().request_id;
            $("#btn-confirm-cancellation").data({id});
            $(".modal-body a.reschedule").attr({href: `${window.basePath || ""}/requests/update/${id}`});
            var verbiage = `Are you sure you want to cancel this request for
                <span class="request-description">${language} for a ${type} on ${date} at ${time}</span>?`;
            $("#confirmation-message").html(`<p>${verbiage}</p>`);
            $("#modal-confirm-cancel").modal();
        })/**
         * toggles display of additional defendant-names
         */
        .on("click", "a.expand-deftnames", function(e){
            e.preventDefault();
            $(this).hide().siblings().slideDown();
        })
        .on("click","a.collapse-deftnames", function(e){
            e.preventDefault();
            var self = $(this);
            self.hide().siblings().not(":first-of-type").slideUp(
                function(){self.siblings("a.expand-deftnames").show();}
            );
        });

        $("#btn-confirm-cancellation").on("click",function(){
            console.warn("cancelling shit");
            var id = $(this).data().id;
            var csrf = $("#requests-table").data("csrf");
            var description = $("#modal-confirm-cancel .modal-body p span.request-description").text();
            $.post( `${window.basePath || ""}/requests/cancel/${id}`,{description, csrf})
                .done(function(response){
                    if ("success"===response.status) {
                        return window.location.reload();
                    }
                    /** @todo DRY this out */
                    if ("error" === response.status) {
                        var message = response.message ||
                        `There was an error while processing your last request.
                        Please try again. If the problem persists please contact your site administrator.`
                        $("#error-message").text(message);
                        $("#error-div h3").text("error");
                        $("#error-div").show();
                    }
                })
                .fail(fail)
                .complete(()=>{$("#modal-confirm-cancel").modal("hide")});
        })









});
