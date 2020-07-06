/**
 * public/js/event-form.js. depends on form-utilities.js et al
 */

/*
global $, fail, displayValidationErrors, formatDocketElement, parseTime, toggleSelectClass
*/


/**
* initializes event handlers for the "event" form
*
* still need to: deal with the fact that some of this doesn't apply
* to "request" form
* @return {object}
*/
var eventForm = (function () {
    "use strict";
    /**
    * the event update|create form
    * @type {jQuery}
    */
    var form = $("#event-form");

    /**
    * parent location select element
    *
    * the general location/building where specific location is found
    * @type {jQuery}
    */
    var parentLocationElement = $("#parent_location");

    /**
    * location select element
    * @type {jQuery}
    */
    var locationElement = $("#location");

    /**
    * event-type select element
    * @type {jQuery}
    */
    var event_type_element = $("#event_type");

    /**
    * language select element
    * @type {jQuery}
    */
    var languageElement = $("#language");

    /**
    `* judge select element
    * @type {jQuery}
    */
    var judgeElement = $("#judge");

    /**
    * hidden flag for whether a generic, "anonymous" judge is selected
    *
    * sometimes also known as pseudojudge. yeah I know.
    * @type {jQuery}
    */
    var anon_judge = $("#is_anonymous_judge");

    /**
    * interpreter select element
    *
    * note: this is for admin mode only
    * @type {jQuery}
    */
    var interpreterSelectElement = $("#interpreter-select");

    /**
    * button for adding the selected interpreter to the event
    *
    *  note: this is for admin mode only
    * @type {jQuery}
    */
    var interpreterButton = $("#btn-add-interpreter");

    /**
    * the "hat" select element
    * the title|description|category (a/k/a hat) of the person who
    * submitted the request to schedule an interpreter. for admin mode only.
    * @type {jQuery}
    */
    var hatElement = $("#hat");

    /**
    * initial value of hat element.
    *
    * for admin mode only.
    * @type {integer}
    */
    var hat_id = hatElement.val();

    /**
    * submitter -- the person (or dept etc) who requested an interpreter
    *
    * admin mode only
    *
    * @type {jQuery}
    */
    var submitterElement = $("#submitter");

    /**
    * initial value of submitter element
    *
    * admin mode only
    *
    * @type {integer}
    */
    var submitter_id = submitterElement.val();

    /**
     * button for defendant-name search
     * @type {jQuery}
     */
    var defendantSearchElement = $("#defendant-search");

    /**
     * div for containing defendant-name search results
     * @type {jQuery}
     */
    var slideout = $("#slideout-toggle");

    /**
     * callback for parent location "change" event
     *
     * @param  {object} event
     * @param  {object} params
     * @todo: cache
     * @return {void}
     */
    var parentLocationChange = function (event, params) {

        if (!parentLocationElement.val()) {
            locationElement.val("").attr({disabled : "disabled"});
        } else {
            locationElement.removeAttr("disabled");
            // populate with children of currently selected parent location
            $.getJSON("/locations/get-children",
                {parent_id : parentLocationElement.val()},
                function (data) {
                    var options = data.map(function (item) {
                        return $("<option>").val(item.value)
                            .text(item.label)
                            .data({type: item.type});
                    });
                    // discard existing option elements (for now)
                    locationElement.children().slice(1).remove();
                    locationElement.append(options)
                    // custom event doesn't do anything yet
                        .trigger("sdny.location-update-complete");
                    // if we were triggered with a location_id to set...
                    if (params && params.location_id) {
                        locationElement.val(params.location_id)
                            .removeClass("text-muted");
                    }
                }
            );
        }
    };

    /**
     * callback for assign-interpreter button's click event
     *
     * fetches from server and inserts markup containing human-readable label,
     * interpreter id, and a button for removing the interpreter
     *
     * for admin mode only
     *
     * @param  {object} event
     * @return {void}
     */
    var interpreterButtonClick = function(event,params){
        var id = interpreterSelectElement.val();
        if (! id ) { return; }
        var banned_by = interpreterSelectElement.children(":selected").data("banned_by");
        if (banned_by) {
            //check_banned_list(event);
            /** @todo then figure out what to do next */
        } else {
            console.debug("proposed interpreter has no banned-by data");
        }
        var selector = `#interpreters-assigned li > input[name$="[interpreter]"][value="${id}"]`;
        if ($(selector).length) {
            // duplicate. maybe do something to let them know?
            return interpreterSelectElement.val("");
        }
        var name = interpreterSelectElement.children(":selected").text();
        var last = $("#interpreters-assigned li > input").last();
        var index;
        if (last.length) {
            var m = last.attr("name").match(/\[(\d+)\]/);
            if (m.length) {
                index = parseInt(m.pop()) + 1;
            } //else { // this is an error. to do: do something  }
        } else {
            index = 0;
        }
        interpreterSelectElement.val("");
        // get the markup. this approach is kind of stupid.
        $.get("/admin/schedule/interpreter-template",
            {   interpreter_id : id, index, name,
                event_id : $("#event_id").val()
            })
            .then((html)=>{
                var el = $(html);
                if (banned_by) { el.data({banned_by});}
                $("#interpreters-assigned").append(el);
                // if (params && params.submit) {
                //     $("#event-form input[value=save]").trigger("click");
                // }
            })
            .fail(fail);
    };

    // $("#event_type, #judge, #submitter").on("change",function(e){
    //     // only deal with "natural" events, not el.trigger("change")
    //     if (! e.originalEvent) { return; }
    //     // e.target is the element that changed
    //     check_banned_list(e);
    //
    // });

    /**
     * callback for language-select's change event
     *
     * repopulates the interpreter select element according to the language
     *
     * for admin mode only
     *
     * @param  {object} event
     * @param  {object} params
     * @return {void}
     */
    var languageElementChange = function(event,params) {
        var language_id = languageElement.val();
        // remove the interpreters/options if the language changes, except
        // when we're initially triggered on page load, which we find out
        // from the "params" parameter
        if (! params || params.remove_existing !== false) {
            $("#interpreters-assigned li").remove();
        }
        // no language? no interpreters
        if (! language_id) {
            interpreterSelectElement.attr("disabled","disabled");
            return;
        }
        // fetch interpreter data to populate interpreter-select element
        $.getJSON("/admin/schedule/interpreter-options?language_id="+language_id)
            .then(
                function(data){
                    var options = data.map(function(item){
                        var opt = $("<option>").val(item.value).text(item.label);
                        if (item.attributes) {
                            for (let [key, value] of Object.entries(item.attributes)) {
                                opt.attr(key,value);
                            }
                        }
                        return opt;
                    });
                    interpreterSelectElement.children().not(":first").remove();
                    interpreterSelectElement.append(options)
                        .trigger("sdny.language-update-complete");
                    // ^ ...doesn't currently do anything -- maybe later
                    if (options.length) {
                        interpreterSelectElement.removeAttr("disabled");
                    }
                }
            );
    };

    /**
     * callback for judge "change" event
     *
     * for admin mode only
     *
     * @param  {object} event
     * @return {void}
     */
    var judgeElementChange = function() {
        if (!  judgeElement.val()) {
            // return;
            // note to self:  decide: why (not) return if no judge is selected?
        }
        // keep track of whether judge is a person or a generic/pseudojudge
        anon_judge.val(
            judgeElement.children(":selected").data("pseudojudge") ? 1 : 0
        );
        var judge = judgeElement.children(":selected");
        var is_magistrate = judge.data("pseudojudge") &&
            judge.text().toLowerCase().indexOf("magistrate") > -1;
        // when it's the magistrate, set the courthouse if possible
        /** to do: start loading location_type_id as parent_location data element
            so that we can know whether to switch courthouses if they change
            from one generic magistrate to the other?
        */
        if (is_magistrate  && !parentLocationElement.val()) {
            //console.log("shit is Magistrate... ");
            var location_id = judge.data("default_parent_location")
                || judge.data("default_location");
            parentLocationElement.val(location_id)
                .trigger("change", location_id ? {location_id:location_id}:null);
            return;
        }
        if (! event_type_element.val() ||
            "in" !== event_type_element.children(":selected").data().category) {
            return;
        }
        /*
          * We are dealing with an in-court event
          * If the currently selected judge has a default location...
          */
        var judge_parent_location = judge.data("default_parent_location");
        var judge_default_location = judge.data("default_location");
        var current_parent_loc_id = parseInt(parentLocationElement.val());
        if (judge_parent_location) {
            /* and that default's ~parent~ location is other than the
              * currently selected parent location...
              */
            if (judge_parent_location !== current_parent_loc_id) {
                /* then set the parent location to the current judge's default... */
                parentLocationElement.val(judge_parent_location)
                /* and trigger its "change" event, passing the handler the
                * currently selected judge's default location, if any
                */
                    .trigger("change", judge_default_location ?
                        { location_id:judge_default_location } : null);
                return;
            } else { // same parent location, just update the courtroom
                locationElement.val(judge_default_location);
            }
        }
    };

    /**
     * callback for hat-select's change event
     *
     * gets data to update submitter dropdown based on selected hat
     *
     * for admin mode only
     *
     * @param  {object} event
     * @return {void}
     */
    var hatElementChange = function() {

        var init_values = submitterElement.data();
        var anonymity = hatElement.children(":selected").data("anonymity");
        if (anonymity === 1) {
            submitterElement.attr("disabled","disabled")
                .next("button").attr({hidden:true});
            return;
        } else {
            submitterElement.removeAttr("disabled");
        }
        var hat_id = $(this).val();
        if (! hat_id) {
            submitterElement.children().not(":first").remove();
            $("#submitter + button").attr("hidden",true)
            return;
        } else {
            // if the initial "submitter" value was an inactive person, extra
            // effort is needed to fetch the person again if they change the "hat"
            // and then change it back
            if (init_values && init_values.hat_id === hat_id) {
                var person_id = init_values.submitter_id;
            } else {
                person_id = null;
            }
        }
        $.getJSON("/admin/people/get",
            { hat_id: hat_id, person_id : person_id },
            function(data)
            {
                var options = data.map(function(item){
                    return $("<option>").val(item.value)
                        .text(item.label)
                        .data({type: item.type});
                });
                submitterElement.removeAttr("disabled");
                submitterElement.children().not(":first").remove();
                submitterElement.append(options)
                    .trigger("sdny.submitter-update-complete");
                $("#submitter + button").popover({
                    html : true,
                    sanitize: false,
                    content: get_submitter_help_message,
                    trigger : 'focus',
                    placement : 'top',
                    title: '',
                    container:'body'
                }).attr({hidden:false});
            }
        );

    };

    // this is going to become dynamic
    /**
     * @return {string}
     */
    const get_submitter_help_message = function(){
        var hat_option = hatElement.children(":selected");
        var data = hat_option.data();
        var label = hat_option.text().toLowerCase();
        var html = 'whatever';
        switch (data.role) {
            case "submitter":
            case "manager":
            case "staff":
                html = `<div class="popover-submitter-admin">To enter the name of a court employee who is not
                found in the select menu, please go to <a href="${window.basePath}/admin/users">user administration</a>.</div>`;
                break;
            default :
                if (label.indexOf("interpreter") > -1) {
                    html = `<div class="popover-submitter-admin">To enter the name of a court interpreter who is not
                    found in the select menu, please go to <a href="${window.basePath}/admin/interpreters">the interpreter roster</a>.</div>`;
                } else {
                    html = `<div class="popover-submitter-admin">To enter the name of a person who is not
                    found in the select menu, please go to <a href="${window.basePath}/admin/people">admin/people</a>.</div>`;;
                }
        }
        return html;
    };
    /**
     * callback for form's submit event
     *
     * admin mode only
     *
     * @param  {object} event
     * @return {void}
     */
    var formSubmit = function(event){

        event.preventDefault();
        console.log("this is SUBMIT event, yes?");
        // return false;
        if (! locationElement.val()) {
            // no specific location was selected, so the general location
            // should be submitted in its place
            var location_id = parentLocationElement.val();
            if (location_id) {
                locationElement.after(
                    $("<input>").attr({
                        name : "event[location]",
                        type : "hidden"
                    }).val(location_id)
                );
            }
            if (form.data("deftnames_modified")) {
                // hint to the controller that there was an update
                // even though may look like like there wasn't. experimental.
                form.append(
                    $("<input>")
                        .attr({name:"deftnames_modified",type:"hidden"}).val(1)
                );
            }
        }
        // if there is no judge selected, clear this so form validation
        // doesn't give us false positive followed by Event entity exception
        // due to both judge and anon judge props being null
        if (! judgeElement.val()) {
            anon_judge.val(0);
            $("#anonymous_judge").val(judgeElement.val());
        }

        // multi-date stuff
        if (! form.data("multiDate")) {
            $("li.multidate, input[name=\"event[dates]\"]").remove();
        }

        // and now...
        var data = form.serialize();
        var url = form.attr("action");
        $.post(url,data).done(function(response) {
            if (response.validation_errors) {
                return displayValidationErrors(response.validation_errors);
            }
            document.location = `${window.basePath}/admin/schedule/view/${response.id}`;
        })
            .fail(fail);
    };

    /**
     * callback for defendant search result "show" event
     * @return {void}
     */
    var onDeftSlideoutShow = function(){
        if ($("#slideout-toggle li").length) {
            $("#slideout-toggle li a").first().focus();
        }
    };

    /**
     * callback for defendant-name search button's "click" event
     * @return {void}
     */
    var deftNameSearchButtonClick = function() {
        // get rid of the new name insertion form, if it exists
        $("#deftname-form-wrapper").remove();
        if ($("#btn-add-defendant-name").attr("disabled")) {
            $("#btn-add-defendant-name").removeAttr("disabled aria-disabled");
        }

        var name = defendantSearchElement.val().trim();
        if (! name) {
            defendantSearchElement.val("").attr({placeholder:"enter a lastname to search for"});
            return;
        }
        $.get("/defendants/search",{term:name,page:1},
            function(data){
                slideout.css("width","");
                $("#slideout-toggle .result").html(data);
                if (! slideout.is(":visible")) {
                    slideout.toggle("slide",onDeftSlideoutShow);
                }
                if (! $("#slideout-toggle .result").is(":visible")) {
                    $("#slideout-toggle .result").show();
                }
            }
        );
    };

    /**
     * defendant name auto-complete options
     * @type {Object}
     */
    var deftname_autocomplete_options = {
        source: "/defendants/autocomplete",
        //source: ["Apple","Banana","Bahooma","Bazinga","Coconut","Dick"],
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            appendDefendant({
                name : ui.item.label,
                id : ui.item.value,
                namespace : "event"
            });
            $(this).val("");

        },
        focus: function(event,ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
        },
        open : function() {
            if (slideout.is(":visible")) {
                slideout.hide();
            }
        }
    };

    /**
     * enables mulite-date selection
     *
     * @param  {object} e event
     * @return {void}
     */
    var init_multidate = function(e){
        if (form.data().multiDate) { return };
        if ($("div#error_date:visible")) {
            $("div#error_date").hide();
        }
        $(this).attr({disabled:true});
        form.data({multiDate:true});
        enable_multidate("event",{});
        if (!$("#btn-cancel-multi-date").is(":visible")) {
            $("#btn-cancel-multi-date").parent().removeAttr("hidden");
        }
        var that = this;
        $("#btn-cancel-multi-date").on("click",(e)=>{
            disable_multidate();
            form.data({multiDate:false});
            $(that).removeAttr("disabled");
        })
    };

    /**
     * gets and inserts markup for defendant name
     * @param {object} data
     */
    var appendDefendant = function(data){

        var existing = $(`#defendant-names input[value=${data.id}]`);
        if (existing.length) {
            var span = existing.siblings(".deft-name");
            span.css({backgroundColor:"yellow"});
            window.setTimeout(()=>span.css({backgroundColor:"inherit"}),
                1500);
            return;
        }
        $.get("/defendants/render",
            {
                id : data.id,
                namespace : "event",
                name: data.name || data.surnames + ", "+ data.given_names
            },
            function(html){
                $("#defendant-names").append(html);
                defendantSearchElement.val("");
                if (slideout.is(":visible")) {
                    slideout.toggle("slide",
                        function(){$("#deftname-form-wrapper").remove();});
                }
            }
        );
    };

    /**
     * initializes form state and event handlers
     * @return {void}
     */
    var init = function() {
        $("body").on("mousedown",".popover-submitter-admin a",function(e){
            document.location = e.target.href;
        });
        form.data({"multiDate":false});
        $("input.docket").on("change",formatDocketElement);

        $("input.date").datepicker({
            changeMonth: true,
            changeYear: true,
            selectOtherMonths : true,
            showOtherMonths : true
        });
        // if it's a "create", initialize support for multi-date selection
        $("#btn-multi-date").on("click",init_multidate);

        $("input.time").on("change",parseTime);

        $("select").on("change",toggleSelectClass).trigger("change");

        /* ============  stuff related to defendant names =======================*/

        /** deft name autocompletion */
        $("#defendant-search")
            .on("keypress",(e)=>{ if (e.which === 13) { e.preventDefault();} })
            .autocomplete(deftname_autocomplete_options);

        /** =========  display defendant-name search results   ==============*/
        $("#btn-defendant-search").on("click",deftNameSearchButtonClick);
        /** =================================================================*/

        /** defendant search result: pagination links ========================*/
        slideout.on("click",".pagination a",function(event){
            event.preventDefault();
            $("#slideout-toggle .result").load(this.href,onDeftSlideoutShow);
        });

        /** listener for deft name search result items */
        slideout.on("click",".defendant-names li",function(){
            var element = $(this);
            //alert(element.data("id")); return;
            $.get("/defendants/render",
                {
                    name : element.text().trim(),
                    id : element.data("id")
                },  function(html){
                    $("#defendant-names").append(html);
                    defendantSearchElement.val("");
                    slideout.toggle("slide");
                }
            );
        });

        /* ==================== */
        $("#slideout-toggle .close").on("click",
            function(){slideout.toggle("slide");}
        );

        languageElement.on("change",languageElementChange);
        if (! languageElement.val()) {
            interpreterSelectElement.attr("disabled","disabled");
        } else {
            languageElement.trigger("change",{remove_existing:false});
        }

        if (! parentLocationElement.val()){
            locationElement.val("").attr({disabled : "disabled"});
        }

        parentLocationElement.on("change",parentLocationChange);


        // interpreter and deft name "remove" buttons event handler
        $("#interpreters-assigned, #defendant-names").on("click",".btn-remove-item",
            function(event){
                event.preventDefault();
                $(this).closest(".list-group-item").slideUp(
                    function(){ $(this).remove();} );
            }
        );
        /** for admin mode only */
        interpreterButton.on("click",interpreterButtonClick);

        if (! hat_id) {
            submitterElement.attr({disabled:"disabled"});
        } else {
            if (submitter_id) {
                submitterElement.data({
                    submitter_id : submitter_id,
                    hat_id : hat_id
                });
            }
        }
        if ($("#judge option[selected]").length > 1) {
            // horrible hack, for cases where two judge option elements
            // share the same value. the last one wins, right or wrong, unless
            // we intervene @todo something better, e.g., hack the pseudojudge
            // values by appending an "A" or something and save real value as
            // a data attribute, or some such
            console.log("not good: two options have selected attrib");
            var is_anonymous_judge = $("#anonymous_judge").val() ? true : false;
            var real_judge_value = judgeElement.val();
            var last_selected = $("#judge option[selected]").last();
            if (last_selected.data('pseudojudge')) {
                if (! is_anonymous_judge) {
                    last_selected.removeAttr("selected");
                    judgeElement.val(real_judge_value);
                }
            }
        }

        /** needs revision for request mode */
        judgeElement.on("change",judgeElementChange);
        /** to do: get rid of unnecessary stuff? */
        if (judgeElement.val()) {
            var data = judgeElement.children(":selected").data();
            if (data.pseudojudge) {
                anon_judge.val(1);
                $("#anonymous_judge").val(judgeElement.val());
            }
        }

        /** these next are for admin mode */
        hatElement.on("change",hatElementChange);

        /**
         * try to help them out if they chose an interpreter but
         * did not click the button
         */
        $("input[value=save]").on("click",function(event){
            event.preventDefault();
            console.warn("this is CLICK event on save button");
            var submitButton = $(this);
            var banned_interpreter_found = (function(){
                var opt = $("#interpreter-select option:selected");
                if (! opt.length) { return false; }
                if (! opt.data("banned_by")) { return false; }
                var category = event_type_element.children(":selected").data("category");
                if (! ["in","out"].includes(category)) { return false; }
                var person_id = category === "in" ? judgeElement.val() : submitterElement.val();
                return opt.data("banned_by").toString().split(",").includes(person_id);

            })();
            if ($("#interpreter-select").val() && ! banned_interpreter_found) {
                // if there's a problem with a **banned** interpreter, we just let it go,
                // because the interpreter in question won't get saved anyway.
                event.preventDefault();
                $("#modal-assign-interpreter .modal-footer button").on("click",
                    function(event) {
                        var button = $(event.target);
                        //submitButton.off("click");
                        if (button.text()==="yes") {
                            interpreterButton.trigger("click",{submit:true});
                        } else {
                            $("#interpreter-select").val("");
                            submitButton.trigger("click");
                        }
                    });
                var name = $("#interpreter-select option:selected").text();
                $("#modal-assign-interpreter .modal-body").html(
                    `Did you mean to assign interpreter <strong>${name}</strong> to this event?`);
                $("#modal-assign-interpreter").modal();
                return false;
            }
            // and the stray deft name
            if (defendantSearchElement.val()) {
                console.warn("eat shit?");
                var deft = defendantSearchElement.val().trim();
                var deftname_modal = $("#modal-stray-defendant-name");
                $("#modal-stray-defendant-name").modal({show:false});
                $("#modal-stray-defendant-name .modal-header").html("Name has not been added");
                $("#modal-stray-defendant-name .modal-body").html(`<strong>${deft}</strong> has not yet been added to this event.
                Did you intend to look up this name?`);
                
                var wants_to_submit = false;
                $("#btn-yes-search").on("click",(e)=>{
                    e.preventDefault();
                    wants_to_submit = false;
                    deftname_modal.modal("hide");
                    // deftname_modal.on("hide.bs.modal",()=>{
                    console.warn("FUCK ME? they said YES search");
                    $("#btn-defendant-search").trigger("click");
                    // });
                    // deftname_modal.modal("hide");               
                });
                $("#btn-no-search").on("click",()=>formSubmit(event));
                $("#modal-stray-defendant-name").modal("show");
                if (! wants_to_submit) {
                    return false; 
                } else {
                    // formSubmit(event);
                }
            } else {
                // formSubmit(event);
            }
        });//*/

        // });
        // form.on("submit",formSubmit);
    };

    return {
        init : init,
        defendants : {
            elements : {
                slideout : slideout
            },           //,callbacks : {},
            append : appendDefendant
        },
    };
})();


/**
 * initializes handlers for defendant-name editing
 *
 * depends on a global eventForm. for admin mode only. we need a different
 * approach for Requests mode.
 * @return {object}
 */
var defendantForm = (function(){

    var addDeftCallback = function(response){        
        if (response.validation_errors) {
            displayValidationErrors(response.validation_errors);
            return;
        }
        if (response.data && response.data.id) { // successful insert           
            eventForm.defendants.append({
                id : response.data.id,
                surnames : $("#surnames").val().trim(),
                given_names : $("#given_names").val().trim()
            });
        } 
        if (response.duplicate_entry_error) {
            var existing = response.existing_entity;
            var exact_duplicate =
                existing.surnames ===  $("#surnames").val().trim()
                &&
                existing.given_names ===  $("#given_names").val().trim();
            if (exact_duplicate) {
                eventForm.defendants.append(existing);
            } else { // this is a pain in the ass, but...
                // fix the width to keep it from expanding further
                slideout.css({width:slideout.width()});
                // splice in the name
                $("#deft-existing-duplicate-name").text(
                    existing.surnames + ", "+existing.given_names);
                // disable default button actions (form submission)
                $(".duplicate-name button").on("click",function(event){
                    event.preventDefault();
                });
                if (!$(".duplicate-name").length) {
                    window.alert("we have a problem");
                }
                // display the instructions and options
                $(".duplicate-name").show();

                // easy enough: use the existing name as is
                $("#btn-use-existing").on("click",function(){
                    eventForm.defendants.append(existing);
                });
                // update the entity, then use as modified
                $("#btn-update-existing").data({id:existing.id})
                    .on("click",function(){
                        var url = "/admin/defendants/update-existing/"
                            +$(this).data("id");
                        var data = $("#defendant-form").serialize();
                        $.post(url, data, updateDefendantCallback,"json");
                    });
                // forget the whole thing
                $("#btn-cancel").on("click",function(){
                    slideout.toggle("slide",
                        function(){$("#deftname-form-wrapper").remove();});
                });
                // and if they edit shit, all bets are off
                var div = $("#deftname-editor .modal-body");
                $("#defendant-form").one("change",function(){
                    div.slideUp(function(){
                        div.remove();
                        $("#btn-add-defendant-name").removeAttr("disabled aria-disabled");
                    });
                });
                // disable the button for submitting the form
                //$("#btn-add-defendant-name").attr({disabled:"disabled", "aria-disabled":"true" });
            }
        }
    };

    var updateDefendantCallback = function(response){
        if (response.id) {
            var selector = "input[name=\"event[defendantEvents]["+
                response.id +"]\"]";
            var defendant_name = $("#surnames").val().trim()
                +", "+ $("#given_names").val().trim();
            //console.log("selector is: "+selector);
            if ($(selector).length) {
                // update the existing thingy
                $(selector).val(defendant_name)
                    .next().text(defendant_name);
            } else { // append new thingy
                eventForm.defendants.append({
                    id : response.id,
                    surnames : $("#surnames").val().trim(),
                    given_names : $("#given_names").val().trim()
                });
            }

        } else {  /** error. to do: do something! */   }
    };

    var slideout = eventForm.defendants.elements.slideout;
    /** listener for add-defendant button  */
    slideout.on("click","#btn-add-defendant-name",function(){

        if (! $("#slideout-toggle form").length) {
            // GET the form
            $("#slideout-toggle .result").slideUp(function(){$(this).empty();}).after($("<div/>")
                .attr({id:"deftname-form-wrapper"})
                .load("/admin/defendants/add form",function(){
                    $(this).prepend("<h4 class=\"text-center bg-primary text-white rounded p-1 mt-2\">add new name</h4>");
                })
            );
        } else {
            // POST the form
            var data = $("#defendant-form").serialize();
            $.post("/admin/defendants/add", data, addDeftCallback,
                "json");
        }
    });

    var getEventModificationTime = function(event_id){
        $.get("/admin/schedule/get-modification-time/"+event_id,
            function(response){
                if (response.modified) {
                    var val_before = $("#modified").val();
                    if (val_before != response.modified) {
                        //console.log("updating last modification timestamp!");
                        $("#modified").val(response.modified);
                    } //else {console.log("looks like no update to mod time?");}
                }
            });
    };

    var defendantFormSubmitCallback = function(response) {
        console.debug("this is defendantFormSubmitCallback");
        var defendantForm = $("#defendant-form");
        if (defendantForm.length > 1) {
            console.warn("Oops! More than one #defendant-form in existence.");
        }
        if (response.validation_errors !== undefined) {
            return displayValidationErrors(response.validation_errors);
        }
        if (response.inexact_duplicate_found) {
            console.debug(response.existing_entity);
            var name = response.existing_entity;
            var existing = `${name.surnames}, ${name.given_names}`;
            defendantForm.prepend($("<input>").attr(
                { type:"hidden",
                    name:"duplicate_resolution_required",value:1})
            );
            $("#deft-existing-duplicate-name").text(existing);
            var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
            return $(shit).show();
        }
        // if (response.status !== "success") {
        //     $("#defendant-form-error").html(
        //         "Oops. We got an error message saying:<br><em>"+response.message+"</em>"
        //     ).show();

        //} else {
        /** to do: check for duplicate defendant-name in the form
            before doing this
            */
        var id = $("#deftname-editor input[name=id]").val();
        var input = $("li input[value="+id+"]");
        var span =  input.siblings(".deft-name");
        // sanity check?
        console.warn(`${input.length} input found`);
        var defendant_name = $("#surnames").val().trim()
                +", "+ $("#given_names").val().trim();
            // update the existing thingy
        span.text(defendant_name);
        console.log("updated deft name");
        var new_deft_id = response.insert_id
                || response.deftname_replaced_by;
        if (new_deft_id) {
            input.val(new_deft_id);
            console.log("swapped out deft id with "+new_deft_id);
        }
        $("#defendant-form-success").text("This name has been updated.").show();
        $("#event-form").data({deftnames_modified : 1});
        window.setTimeout(function(){
            $("#defendant-form-success").hide();
            $("#deftname-editor").modal("hide");
        },2000);
        //}
    };

    var loadDefendantNameForm = function()
    {
        $("#deftname-editor").modal("show");
        if ($("#deftname-editor .alert-warning").is(":visible")) {
            if ($("#defendant-form").data("error_not_found")) {
                $("#defendant-form div.alert #error-message").append(
                    " The underlying record might have been deleted.");
            }
            $("#deftname-editor #deftname-editor-submit, .alert button[data-hide]").hide();
            //$("#deftname-editor .modal-footer button[data-dismiss]").text("OK");
            var id = $("#deftname-editor .modal-body").data().id;
            $(`#defendant-names input[value="${id}"]`).next(".btn-remove-item")
                .trigger("click");
            return;
        }
        var docket = $("#docket").val();
        if (docket) {
            $("#occurrences .form-check-input").each(function(){
                if (-1 !== $(this).val().indexOf(docket)) {
                    $(this).attr({checked:"checked"});
                }
            });
        }
        // save the initial state so we can tell if it changed
        $("#given_names").data({was : $("#given_names").val()});
        $("#surnames").data({was : $("#surnames").val()});
    };
    /**
     * submit handler for editing a defendant name within the
     * event edit|update context
     * @return void
     */
    var defendantUpdateSubmit = function()
    {
        var defendantForm = $("#defendant-form");
        // did they really change anything?
        var modified = $("#surnames").val() != $("#surnames").data("was")
            ||  $("#given_names").val() != $("#given_names").data("was");
        if (! modified) {
            var error_div = $("#defendant-form-error");
            if (! error_div.length) {
                error_div = $("<div/>").addClass("alert alert-warning")
                    .attr({id:"defendant-form-error"});
                defendantForm.prepend(error_div);
            }
            $("#defendant-form-error").text(
                "This name has not been modified. Please press cancel "
                +"if you don't need to make any changes.").show();
            return;
        } else { $("#defendant-form-error").hide(); }
        // we repeat ourself... |-:
        var id = $("#deftname-editor input[name=id]").val();
        //console.debug("id is what? "+id);
        var url = `/admin/defendants/edit/${id}?context=events`;
        // we may need to supply an event id
        var event_id = $(`input[name="event[id]"]`).val() || false;
        if (event_id) {
            url += "&event_id="+event_id;
        }
        //console.debug("we are in defendantUpdateSubmit, about to post");
        $.post(url, defendantForm.serialize(), defendantFormSubmitCallback,"json")
        .then(function(){getEventModificationTime(event_id);});
    };

    var init = function() {

        /** ======  for editing defendant names ================= **/

        // modal closes, remove deft-name form from DOM
        // so that no more than one exists at the same time
        $("#deftname-editor").on("hidden.bs.modal",(event)=>{
            $(`#${event.target.id} .modal-body form`).remove();
        });
        $("#deftname-editor").on("click","#btn-select-all, #btn-invert-selection",
        // if this look familiar, it's because it's found in defendant-form.js
            function(event){
                event.preventDefault();
                var checkboxes = $("form input[type=checkbox]");
                if ($(event.target).attr("id")=="btn-select-all") {
                    checkboxes.prop("checked",true);
                } else {
                    checkboxes.each(function(){
                        var checkbox = $(this);
                        var checked = checkbox.prop("checked");
                        checkbox.prop("checked",!checked);
                    });
                }
            });
        // load deft-name editing form
        $("ul.defendant-names").on("click","li span.deft-name",
            function(){
                var id = $(this).next("input").val();
                var div = $("#deftname-editor .modal-body").data({id});
                var selector = `/admin/defendants/edit/${id} #defendant-form`;
                $("#deftname-editor-submit").show();
                div.load(selector,loadDefendantNameForm);
            }
        );

        $("#deftname-editor-submit").on("click",defendantUpdateSubmit);

    };
    return { init };
})();

$(document).ready(function()
{
    eventForm.init();
    defendantForm.init();
});
