/**
 * public/js/event-form.js
 */

/**
 * initializes event handlers for the "event" form
 *
 * still need to: deal with the fact that some of this doesn't apply
 * to "request" form
 * @return {object}
 */

var $;

var eventForm = (function () {
    "use strict";
    /**
    * the event update|create form
    * @type {jQuery}
    */
    var form = $("#event-form"),

        /**
        * parent location select element
        *
        * the general location/building where specific location is found
        * @type {jQuery}
        */
        parentLocationElement = $("#parent_location"),

        /**
        * location select element
        * @type {jQuery}
        */
        locationElement = $("#location"),

        /**
        * event-type select element
        * @type {jQuery}
        */
        eventTypeElement = $("#event-type"),

        /**
        * language select element
        * @type {jQuery}
        */
        languageElement = $("#language"),

        /**
        * judge select element
        * @type {jQuery}
        */
        judgeElement = $("#judge"),

        /**
        * hidden flag for whether a generic, "anonymous" judge is selected
        *
        * sometimes also known as pseudojudge. yeah I know.
        * @type {jQuery}
        */
        anon_judge = $("#is_anonymous_judge"),

        /**
        * interpreter select element
        *
        * note: this is for admin mode only
        * @type {jQuery}
        */
        interpreterSelectElement = $("#interpreter-select"),

        /**
        * button for adding the selected interpreter to the event
        *
        *  note: this is for admin mode only
        * @type {jQuery}
        */
        interpreterButton = $("#btn-add-interpreter"),

        /**
        * the "hat" select element
        * the title|description|category (a/k/a hat) of the person who
        * submitted the request to schedule an interpreter. for admin mode only.
        * @type {jQuery}
        */
        hatElement = $("#hat"),

        /**
        * initial value of hat element.
        *
        * for admin mode only.
        * @type {integer}
        */
        hat_id = hatElement.val(),

        /**
        * submitter -- the person (or dept etc) who requested an interpreter
        *
        * admin mode only
        *
        * @type {jQuery}
        */
        submitterElement = $("#submitter"),

        /**
        * initial value of submitter element
        *
        * admin mode only
        *
        * @type {integer}
        */
        submitter_id = submitterElement.val(),

        /**
         * button for defendant-name search
         * @type {jQuery}
         */
        defendantSearchElement = $("#defendant-search"),

        /**
         * div for containing defendant-name search results
         * @type {jQuery}
         */
        slideout = $("#slideout-toggle"),

        /**
         * callback for parent location "change" event
         *
         * @param  {object} event
         * @param  {object} params
         * tofuckingdo: cache
         * @return {void}
         */
        parentLocationChange = function (event, params) {
            /*jslint unparam: true */

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
    /*jslint unparam: false */

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
    var interpreterButtonClick = function(){
        var id = interpreterSelectElement.val();
        if (! id ) { return; }
        var selector = "#interpreters-assigned li > input[value=\""+id+"\"]";
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
        // get the markup
        //** to do: think about using Vue and a component for this and similar */
        $.get("/admin/schedule/interpreter-template",
            {   interpreter_id : id, index : index,
                name : name,
                event_id : $("#event_id").val()},
            function(html){
                $("#interpreters-assigned").append(html);
            });
    };

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

        if (! language_id) {
            interpreterSelectElement.attr("disabled","disabled");
            return;
        }
        $.getJSON("/admin/schedule/interpreter-options?language_id="+language_id)
            .success(
                function(data){
                    var options = data.map(function(item){
                        return $("<option>").val(item.value).text(item.label);
                    });
                    interpreterSelectElement.children().not(":first").remove();
                    interpreterSelectElement.append(options)
                        .trigger("sdny.language-update-complete");
                    // ^ ...doesn't yet do anything
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
            //return;
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
        if (! eventTypeElement.val() ||
            "in" !== eventTypeElement.children(":selected").data().category) {
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
        console.log("the FUCK???");
        if (anonymity === 1) {
            submitterElement.attr("disabled","disabled");
            return;
        } else {
            submitterElement.removeAttr("disabled");
        }
        var hat_id = $(this).val();
        if (! hat_id) {
            hatElement.children().not(":first").remove();
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
            }
        );
    };

    /**
     * callback for form's submit event
     *
     * admin mode only
     *
     * @param  {object} event
     * @return {void}
     */
    var formSubmit = function(){

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
            $("#anonymousJudge").val(judgeElement.val());
        }
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
            var that = $(this);
            $.get(
                "/defendants/template",
                {id:ui.item.value,name:ui.item.label},
                function(html){
                    $("#defendant-names").append(html);
                    that.val("");
                }
            );
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
     * gets and inserts markup for defendant name
     * @param {object} data
     */
    var appendDefendantName = function(data){
        $.get("/defendants/template",
            {   id: data.id,
                name: data.surnames + ", "+ data.given_names
            },
            function(html){
                $("#defendant-names").append(html);
                defendantSearchElement.val("");
                slideout.toggle("slide",
                    function(){$("#deftname-form-wrapper").remove();});
            });
    };

    /**
     * tries to figure out time of day based on string
     * @param  {object} event
     * @return {void}
     */
    var parseTime = function(event)
    {
        var timeElement = $(event.target);
        var div = timeElement.closest("div.form-group");
        var errorDiv = timeElement.next(".validation-error");
        if (! errorDiv.length) {
            timeElement.after($("<div>").addClass("alert alert-warning validation-error"));
            errorDiv = timeElement.next(".validation-error");
        }
        var time = timeElement.val().trim();
        if ("" === time) {
            return;
        }
        var re = /^(0?[1-9]|1[0-2]):?([0-5]\d)?\s*((a|p)m?)?$/i;
        var hour; var minute; var ap;
        var matches = time.match(re);
        if (matches)
        {
            hour = matches[1];
            if ("0" === hour[0]) { // no leading zero
                hour = hour.substring(1);
            }
            minute = matches[2] ? matches[2] : "00";
            ap = matches[3];
            if (!ap) {
                if (hour === "12") {
                    ap = "pm";
                } else {
                    ap = hour < 9 ? "pm" : "am";
                }
            } else if (ap.length === 1) {
                ap = ap + "m";
            }
        } else {
            matches = time.match(/^([01][0-9]|2[1-3])([0-5][0-9])$/);
            if (matches) {
                hour = matches[1];
                ap = "am";
                if (hour > 12) {
                    hour -= 12;
                    ap = "pm";
                }
                minute = matches[2];
            }
        }
        if (! matches) {
            errorDiv.addClass("alert alert-warning validation-error")
                .text("invalid time").show();
            return;
        }
        div.removeClass("alert alert-warning validation-error");
        errorDiv.empty().hide();
        timeElement.val((hour + ":" + minute + " " + ap).toLowerCase());
    };

    /**
     * regular expression for docket numbers
     * @type {object} regular expression
     */
    var DocketRegExp = /^(?:s-?[1-9] *)?((?:19|20)?\d{2})[- .]*(c(?:r(?:im)?|i?v)|m(?:ag|j)?)[- .]*(\d{1,5})(?: *\([a-z]{2,3}\))?$/i;
    /**
     * tries to format a string as a US District Court docket number
     * @param  {object} event
     * @return {jQuery} event's target element
     */
    var formatDocketElement = function(event)
    {
        var element = $(event.target);
        var div = element.closest("div.form-group");
        var errorDiv = element.next(".validation-error");
        if (! errorDiv.length) {
            // try something else
            errorDiv = $("#docket").parent().next(".validation-error");
            if (! errorDiv.length)  {
                // last resort
                element.after($("<div>").addClass("alert alert-warning validation-error"));
                errorDiv = element.next(".validation-error");
            }
        }
        element.val(element.val().trim());
        if (! element[0].value ) {
            errorDiv.empty().hide();
            element.data("valid",1);
            return element;
        }
        var matches = element[0].value.match(DocketRegExp);
        if (element[0].value && ! matches) {
            errorDiv.text("invalid docket number").show().trigger("show");
            element.data("valid",0);
            div.addClass("has-error has-feedback");
            return;

        } else {
            div.removeClass("has-error has-feedback");
            var year = matches[1];
            var flavor = matches[2];
            var number = matches[3];
        }
        if (year.length === 2) {
            year = year <= 50 ? "20"+year : "19"+year;
        }
        flavor = flavor.toUpperCase();
        if (-1 !== flavor.indexOf("CR")) {
            flavor = "CR";
        } else if (flavor[0] === "M") {
            flavor = "MAG";
        } else {
            flavor = "CIV";
        }
        if (number.length < 4) {
            var padding = new Array(5 - number.length).join("0");
            number = padding + number;
        } else if (number.length === 5) {
            // four digits with up to three leading zeroes is enough
            number = number.replace(/^00/,"0");
        }
        element.val(year + "-"  + flavor + "-" + number)
            .data("valid",1);
        errorDiv.empty().hide();
        return element;
    };

    /**
     * initializes form state and event handlers
     * @return {void}
     */
    var init = function() {

        $("input.docket").on("change",formatDocketElement);

        $("input.date").datepicker({
            changeMonth: true,
            changeYear: true,
            selectOtherMonths : true,
            showOtherMonths : true
        });

        $("input.time").on("change",parseTime);

        $("input.docket").on("change",formatDocketElement);

        $("select").on("change",function(){
            var element = $(this);
            if (element.val()) {
                element.removeClass("text-muted");
            } else {
                element.addClass("text-muted");
            }
        }).trigger("change");
        /* ============  stuff related to defendant names =======================*/

        /** deft name autocompletion */
        $("#defendant-search").autocomplete(deftname_autocomplete_options);

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
            $.get("/defendants/template",
                {id:element.data("id"),name:element.text()},
                function(html){
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

        if (! languageElement.val()) {
            interpreterSelectElement.attr("disabled","disabled");
        }

        if (! parentLocationElement.val()){
            locationElement.val("").attr({disabled : "disabled"});
        }

        parentLocationElement.on("change",parentLocationChange);

        languageElement.on("change",languageElementChange);

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
        /** needs revision for request mode */
        judgeElement.on("change",judgeElementChange);

        // initialize this stuff
        /** to do: get rid of unnecessary stuff? */
        if (judgeElement.val()) {
            var data = judgeElement.children(":selected").data();
            if (data.pseudojudge) {
                anon_judge.val(1);
                $("#anonymousJudge").val(judgeElement.val());
            }
        }
        /** these next are for admin mode */
        hatElement.on("change",hatElementChange);

        form.on("submit",formSubmit);
    };

    return {
        init : init,
        defendants : {
            elements : {
                slideout : slideout
            },           //,callbacks : {},
            append : appendDefendantName
        },
    };
})();


/**
 * initializes handlers for defendant-name editing
 *
 * depends on a global eventForm. for admin mode only. we need a different
 * approach for request mode.
 * @return {object}
 */
var defendantNameForm = (function(){

    var addDeftnameCallback = function(response){

        var defendantForm = $("#defendant-form");
        if (response.validation_errors) {
            displayValidationErrors(response.validation_errors);
            return;
        }
        if (response.id) { // successful insert
            eventForm.defendants.append({
                id : response.id,
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
                        $.post(url, data, updateDefendantNameCallback,"json");
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

    var updateDefendantNameCallback = function(response){
        if (response.id) {
            var selector = "input[name=\"event[defendantNames]["+
                existing.id +"]\"]";
            var defendant_name = $("#surnames").val().trim()
                +", "+ $("#given_names").val().trim();
            console.log("selector is: "+selector);
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
    /** listener for add-defendant-name button  */
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
            $.post("/admin/defendants/add", data, addDeftnameCallback,
                "json");
        }
    });

    var getEventModificationTime = function(event_id){
        $.get("/admin/schedule/get-modification-time/"+event_id,
            function(response){
                if (response.modified) {
                    var val_before = $("#modified").val();
                    if (val_before != response.modified) {
                        console.log("updating last modification timestamp!");
                        $("#modified").val(response.modified);
                    } else {
                        console.log("looks like no update to mod time?");
                    }
                }
            });
    };
    var defendantFormSubmitCallback = function(response) {
        if (response.validation_errors !== undefined) {
            return displayValidationErrors(response.validation_errors);
        }
        if (response.inexact_duplicate_found) {
            var existing = response.existing_entity;
            defendantForm.prepend($("<input>").attr({type:"hidden",name:"duplicate_resolution_required",value:1}));
            $("#deft-existing-duplicate-name").text(existing);
            var shit = "p.duplicate-name-instructions, .duplicate-resolution-radio";
            return $(shit).show();
        }
        if (response.status !== "success") {
            $("#defendant-form-error").html(
                "Oops. We got an error message saying:<br><em>"+response.message+"</em>"
            ).show();
            console.debug(response);
        } else {
            /** to do: check for duplicate defendant-name in the form
            before doing this
            */
            console.log("looking good, bitch!");
            var id = $("#deftname-editor input[name=id]").val();
            var selector = "input[name=\"event[defendantNames][" +
                id +"]\"]";
            var input = $(selector);
            var defendant_name = $("#surnames").val().trim()
                +", "+ $("#given_names").val().trim();
                // update the existing thingy
            input.val(defendant_name)
                .next().text(defendant_name);
            var new_deft_id = response.insert_id || response.deftname_replaced_by;
            if (new_deft_id) {
                var name = input.attr("name").replace(id, new_deft_id);
                input.attr({name : name });
                console.log("id was " + id);
                console.log("input name attrib is now: "+input.attr("name"));
            }
            $("#defendant-form-success").text("This name has been updated.").show();
            $("#event-form").data({deftnames_modified : 1});
            window.setTimeout(function(){
                $("#defendant-form-success").hide();
                $("#deftname-editor").modal("hide");

            },2000);
        }
    };
    var defendantUpdateSubmit = function()
    {
        // did they really change anything?
        var modified = $("#surnames").val() != $("#surnames").data("was")
            ||  $("#given_names").val() != $("#given_names").data("was");
        if (! modified) {
            $("#defendant-form-error").text(
                "This name has not been modified. Please press cancel "
                +"if you don't need to make any changes.").show();
            return;
        } else { $("#defendant-form-error").hide(); }
        // we repeat ourself... |-:
        var id = $("#deftname-editor input[name=id]").val();
        var url = "/admin/defendants/edit/"+ id +"?context=events";
        // we may need to supply an event id
        var event_id = $("input[name=\"event[id]\"]").val() || false;
        if (event_id) {
            url += "&event_id="+event_id;
        }
        var defendantForm = $("#defendant-form");
        /*
        console.log("first one...");
        $.get('/').done(function(x){
          console.log("first one done, second...");
          $.get('/login');
        }).done(function(x){
          console.log("second done");
        });
        */
        $.post(url,defendantForm.serialize(),
            defendantFormSubmitCallback,
            "json")
            .success(function(){
                getEventModificationTime(event_id);
            });
    };

    var init = function() {

        /** ======  for editing defendant names ================= **/

        var submitButton = $("#deftname-editor-submit");
        var cancelButton = submitButton.next("button");

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
        $("ul.defendant-names").on("click","li.defendant span",
            function(){
                var div = $("#deftname-editor .modal-body");
                var id = $(this).data("id");
                var selector = "/admin/defendants/edit/"+ id + " #defendant-form";
                var that = this;
                $("#deftname-editor-submit").show();
                div.load(selector,function()
                {
                    $("#deftname-editor").modal("show");
                    if ($("#defendant-form").data("status")=="NOT FOUND") {
                        $("#defendant-form div.alert").append(
                            " The underlying record might have been deleted out from under you. Please try again.");
                        submitButton.hide();
                        cancelButton.text("OK").one("click",function(){
                            // we have said this very snippet before, but...
                            $(that).closest(".list-group-item").slideUp(
                                function(){$(this).remove();}
                            );
                        });
                        return;
                    }
                    var docket = $("#docket").val();
                    if (docket) {
                        $("#occurrences .form-check-input").each(function(){
                            if (-1 !== $(this).val().indexOf(docket)) {
                                $(this).attr({checked:"checked"});
                            } else {
                                console.log("so, is this a name that has not yet been attached to an event?");
                            }
                        });
                    }
                    // save the initial state so we can tell if it changed
                    $("#given_names").data({was : $("#given_names").val()});
                    $("#surnames").data({was : $("#surnames").val()});
                }
                );
            }
        );

        $("#deftname-editor-submit").on("click",defendantUpdateSubmit);

    };
    return { init : init };
})();

$(document).ready(function()
{
    eventForm.init();
    defendantNameForm.init();
});
