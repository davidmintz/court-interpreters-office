/**
 * public/js/event-form.js
 */

moment = window.moment;
//Modernizr = window.Modernizr;

$(document).ready(function()
{
   // if (! Modernizr.inputtypes.date) {
    $('input.date').datepicker({
        changeMonth: true,
        changeYear: true,
        selectOtherMonths : true,
        showOtherMonths : true
    });
    $('input.date').each(function(i,element){
        if (element.value.match(/^\d{4}-\d\d-\d\d$/)) {
            element.value = element.value.replace(/(\d{4})-(\d\d)-(\d\d)/,"$2/$3/$1");
        }
    });
   // } else { console.log("date element is supported!");}
  //  if (! Modernizr.inputtypes.time) {
        $("input.time")
          .each(function(){formatTimeElement($(this));})
          .on("change",parseTime);
  //  } else {console.log( "time element is supported!"); }
    $('input.docket').on("change",formatDocketElement);

    // toggle the 'text-muted' class on all the select elements
    $('select').on("change",function(){
        var element = $(this);
        if (element.val()) {
            element.removeClass("text-muted");
        } else {
            element.addClass("text-muted");
        }
    }).trigger("change");

    var parentLocationElement = $('#parent_location');
    var locationElement = $('#location');

    parentLocationElement.on("change",function(event,params) {
        if (! parentLocationElement.val()) {
            locationElement.val("").attr({disabled : "disabled"});
        } else {
            locationElement.removeAttr("disabled");
            // populate with children of currently selected parent location
            $.getJSON('/locations/get-children',
                {parent_id:parentLocationElement.val()},
                function(data){
                    var options = data.map(function(item){
                        return $('<option>').val(item.value)
                                .text(item.label)
                                .data({type: item.type});
                    });
                    // discard existing option elements
                    locationElement.children().slice(1).remove();
                    locationElement.append(options)
                         .trigger("sdny.location-update-complete");
                    if (params && params.location_id) {
                        locationElement.val(params.location_id).removeClass("text-muted");
                    }
            });
        }
    });

    if (! parentLocationElement.val()){
        locationElement.val("").attr({disabled : "disabled"});

    } else {
        // on 2nd thought, don't. it unsets the value of #location
        // when we load the form
        // parentLocationElement.trigger("change");
    }
    /** this applies to admin form, not "request" form **/
    var languageElement = $('#language');
    var interpreterSelectElement = $("#interpreter-select");

    if (! languageElement.val()) {
        interpreterSelectElement.attr("disabled","disabled");
    }


    // (re)populate the interpreter select element according to the language
    languageElement.on('change',function(event,params){
        var language_id = languageElement.val();
        // remove the interpreters if the language changes, except
        // when we're initially triggered on page load, which we will
        // find out from the "params" parameter
        if (! params || params.remove_existing !== false) {
            $('#interpreters-assigned li').remove();
        }

        if (! language_id) {
            interpreterSelectElement.attr("disabled","disabled");
            return;
        }
        $.getJSON('/admin/schedule/interpreter-options?language_id='+language_id,
            {}, function(data){
            console.log("WTF?");
            var options = data.map(function(item){
                  return $('<option>').val(item.value).text(item.label);
             });

            interpreterSelectElement.children().not(":first").remove();
            interpreterSelectElement.append(options)
                    .trigger("sdny.language-update-complete");
            if (options.length) {
                interpreterSelectElement.removeAttr("disabled");
            }
        });
    });
    if (languageElement.val()) {
        languageElement.trigger("change",{remove_existing:false});
    }
    var interpreterButton = $('#btn-add-interpreter');
    // add an interpreter to this event
    interpreterButton.on('click',  function(){

        var id = interpreterSelectElement.val();
        if (! id ) { return; }
        var selector = '#interpreters-assigned li > input[value="'+id+'"]';
        if ($(selector).length) {
            // duplicate. maybe do something to let them know?
            return interpreterSelectElement.val("");
        }
        var name = interpreterSelectElement.children(":selected").text();
        var last = $('#interpreters-assigned li > input').last();
        if (last.length) {
            var m = last.attr("name").match(/\[(\d+)\]/);
            if (m.length) {
                index = parseInt(m.pop()) + 1;
            } else {
                // this is an error. to do: do something
            }
        } else {
            index = 0;
        }
        interpreterSelectElement.val("");
        // get the markup
        $.get('/admin/schedule/interpreter-template',
            {   interpreter_id : id, index : index,
                name : name,
                event_id : $('#event_id').val()},
            function(html){
                $('#interpreters-assigned').append(html);
        });
    });
    // interpreter and deft name "remove" buttons event handler
    $('#interpreters-assigned, #defendant-names').on("click",".btn-remove-item",
    function(event){
        event.preventDefault();
        $(this).closest(".list-group-item").slideUp(
              function(){$(this).remove();}
        );
    });

    var hatElement = $('#hat');
    var submitterElement = $('#submitter');
    var hat_id = hatElement.val();
    var submitter_id = submitterElement.val();
    if (! hat_id) {
        submitterElement.attr({disabled:"disabled"});
    } else {
        if (submitter_id) {
            submitterElement.data({
                submitter_id : submitter_id,
                hat_id : hat_id,
            });
        }
    }
    //
    var judgeElement = $('#judge');
    var anon_judge = $('#is_anonymous_judge');
    judgeElement.on('change',function(){
         anon_judge.val(
            judgeElement.children(':selected').data('pseudojudge') ? 1 : 0
        );
    }).trigger('change');

    // get data to update submitter dropdown based on selected hat
    hatElement.on("change",function()
    {
        var init_values = submitterElement.data();
        var anonymity = hatElement.children(':selected').data('anonymity');
        if (anonymity === 1) {
            submitterElement.attr("disabled","disabled");
            return;
        } else {
            submitterElement.removeAttr("disabled");
        }
        var hat_id = hatElement.val();
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
                var person_id = null;
            }
        }
        $.getJSON('/admin/people/get',
                { hat_id: hat_id, person_id : person_id },
                function(data)
                {
                    var options = data.map(function(item){
                        return $('<option>').val(item.value)
                            .text(item.label)
                            .data({type: item.type});
                    });
                    submitterElement.removeAttr("disabled");
                    submitterElement.children().not(":first").remove();
                    submitterElement.append(options)
                           .trigger("sdny.submitter-update-complete");
                }
            );
    });//.trigger('change');
    var eventTypeElement = $('#event-type');

    /**
     * sets the location based on the judge, if possible
     */
    $('#judge').on('change',function(event){

        if (!  judgeElement.val()) {
            return;
        }
        if ( ! eventTypeElement.val()
            || "in" !== eventTypeElement.children(":selected").data().category )
        {
               return;
        }
        var judge_opt = judgeElement.children(':selected');
        var judge_data = judge_opt.data();
        ///*
        var is_magistrate = judge_opt.text().toLowerCase()
             .indexOf('magistrate') !== -1 && judge_opt.data('pseudojudge');
        // if it's magistrate, and they have not set a parent location,
        // let's set it for them
        if (is_magistrate ) {  //&& !parentLocationElement.val()
            //console.log("shit is Magistrate... ");
            parentLocationElement.val(judge_data.default_parent_location || judge_data.default_location)
            .trigger("change", judge_data.default_location ?
                    {location_id:judge_data.default_location} : null);
            return;
         }
        // */
        /* NOTE TO SELF:  try to remember why we are doing this.
         * In English:
         * If the currently selected judge has a default location
         */
        if (judge_data.default_parent_location &&
              /* and that default's ~parent~ location is other than the
               * currently selected parent location...
               */
              judge_data.default_parent_location !== parentLocationElement.val())
        {   /* then set the parent location to the current judge's default... */
            parentLocationElement.val(judge_data.default_parent_location)
            /* and trigger its "change" event, passing the handler the
             * currently selected judge's default location, if any
             */
             .trigger("change", judge_data.default_location ?
                   {location_id:judge_data.default_location} : null
            );
        }
    });

    $("#event-form").on("submit",function(e){
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
        }
    });

    /* ============  stuff related to defendant names =======================*/

    var defendantSearchElement = $('#defendant-search');
    var slideout = $('#slideout-toggle');
    /** deft name autocompletion */
    defendantSearchElement.autocomplete(
        {
                source: '/defendants/autocomplete',
                //source: ["Apple","Banana","Bahooma","Bazinga","Coconut","Dick"],
                minLength: 2,
                select: function( event, ui ) {

                    that = $(this);
                    $.get(
                        '/defendants/template',
                        {id:ui.item.value,name:ui.item.label},
                        function(html){
                            $('#defendant-names').append(html);
                            that.val("");
                        }
                    );
                },
                focus: function(event,ui) {
                    event.preventDefault();
                    $(this).val(ui.item.label);
                },
                open : function() {
                    if (slideout.is(':visible')) {
                        slideout.hide();
                    }
                }
             }
         );
    var onDeftSlideoutShow = function(){

        if ($('#slideout-toggle li').length) {
            $('#slideout-toggle li a').first().focus();
        } else {
            //$('#slideout-toggle h6').hide();
        }
    };
    /* ==================== */
    $('#slideout-toggle .close').on('click',
        function(){slideout.toggle("slide");}
     );
    /** =========  display defendant-name search results   ==============*/
    $('#btn-defendant-search').on("click",function(){
        // get rid of the new name insertion form, if it exists
        $('#deftname-form-wrapper').remove();
        if ($('#btn-add-defendant-name').attr("disabled")) {
             $('#btn-add-defendant-name').removeAttr("disabled aria-disabled");
        }

        var name = defendantSearchElement.val().trim();
        if (! name) {
            defendantSearchElement.val('').attr({placeholder:"enter a lastname to search for"});
            return;
        }
        $.get('/defendants/search',{term:name,page:1},
            function(data){
                slideout.css("width","");
                $('#slideout-toggle .result').html(data);
                console.warn("WTF?");
                if (! slideout.is(':visible')) {
                    slideout.toggle("slide",onDeftSlideoutShow);
                }
                if (! $('#slideout-toggle .result').is(':visible')) {
                    $('#slideout-toggle .result').show();
                }
            });
    });
    /** =================================================================*/

    /** pagination links ================================================*/
    slideout.on('click','.pagination a',function(event){
        event.preventDefault();
        $('#slideout-toggle .result').load(this.href,onDeftSlideoutShow);
    });
    slideout.on('click','.defendant-names li',function(event){
        var element = $(this);
        $.get(
            '/defendants/template',
            {id:element.data('id'),name:element.text()},
            function(html){
                $('#defendant-names').append(html);
                defendantSearchElement.val('');
                slideout.toggle("slide");
            }
        );
    });
    /** =================================================================*/

    /**
     * gets and inserts markup for defendant name
     * @param {object} data
     */
    var append_deft_name = function(data){
        $.get('/defendants/template',
        {   id: data.id,
            name: data.surnames + ", "+ data.given_names
        },
        function(html){
            $('#defendant-names').append(html);
            defendantSearchElement.val('');
            slideout.toggle("slide",
                function(){$('#deftname-form-wrapper').remove();});
        });
    };
    slideout.on('click','#btn-add-defendant-name',function(){

        if (! $('#slideout-toggle form').length) {
            // GET the form
            $('#slideout-toggle .result').slideUp(function(){$(this).empty();}).after($("<div/>")
                .attr({id:'deftname-form-wrapper'})
                .load('/admin/defendants/add form',function(){
                    $(this).prepend('<h4 class="text-center bg-primary text-white rounded p-1 mt-2">add new name</h4>');
                })
            );
        } else {
            // POST the form
            var data = $('#defendant-form').serialize();
            $.post('/admin/defendants/add',data,function(response){
                if (response.validation_errors) {
                    displayValidationErrors(response.validation_errors);
                    return;
                }
                if (response.id) { // successful insert
                    append_deft_name({
                        id : response.id,
                        surnames : $('#surnames').val().trim(),
                        given_names : $("#given_names").val().trim()
                    });
                }
                if (response.duplicate_entry_error) {
                    var existing = response.existing_entity;
                    var exact_duplicate =
                        existing.surnames ===  $('#surnames').val().trim()
                        &&
                        existing.given_names ===  $('#given_names').val().trim();
                    if (exact_duplicate) {
                        append_deft_name(existing);
                    } else { // this is a pain in the ass, but...

                        // fix the width to keep it from expanding further
                        slideout.css({width:slideout.width()});
                        // splice in the name
                        $('#deft-existing-duplicate-name').text(
                            existing.surnames + ', '+existing.given_names);
                        // disable default button actions (form submission)
                        $('.duplicate-name button').on("click",function(event){
                            event.preventDefault();
                        });
                        // display the instructions and options
                        $(".duplicate-name").show();

                        // easy enough: use the existing name as is
                        $('#btn-use-existing').on("click",function(){
                            append_deft_name(existing);
                        });
                        // update the entity, then use as modified
                        $('#btn-update-existing').data({id:existing.id}).on("click",function(){
                            $.post('/admin/defendants/update-existing/'+$(this).data('id'),data,
                            function(response){
                                if (response.id) {
                                    var selector = 'input[name="event[defendantNames]['+
                                        existing.id +']"]';
                                    var defendant_name = $('#surnames').val().trim()
                                        +", "+ $("#given_names").val().trim();
                                    console.log("selector is: "+selector);
                                    if ($(selector).length) {
                                        // update the existing thingy
                                        $(selector).val(defendant_name)
                                            .next().text(defendant_name);
                                    } else { // append new thingy
                                        append_deft_name({
                                            id : response.id,
                                            surnames : $('#surnames').val().trim(),
                                            given_names : $("#given_names").val().trim()
                                        });
                                    }

                                } else {
                                    /** error. @todo do something! */
                                }
                            },'json');
                        });
                        // forget the whole thing
                        $('#btn-cancel').on("click",function(){
                            slideout.toggle("slide",
                            function(){$('#deftname-form-wrapper').remove();});
                        });
                        // and if they edit shit, all bets are off
                        $('#defendant-form').one("change",function(){
                            div.slideUp(function(){
                                div.remove();
                                $('#btn-add-defendant-name').removeAttr("disabled aria-disabled");
                            });
                        });
                        // disable the button for submitting the form
                        $('#btn-add-defendant-name').attr({disabled:"disabled", 'aria-disabled':"true" });
                    }
                }
            },'json');
        }
    });
});

formatTimeElement = function(timeElement) {

    var timeValue = timeElement.val();
    // reformat time;
    if (timeValue && timeValue.match(/^\d\d:\d\d$/)) {
        var formatted = moment(timeValue, 'HH:mm:ss').format('h:mm a');
        //console.log('formatted time is: '+formatted);
        timeElement.val(formatted);
    }
    return timeElement;
};

parseTime = function(event)
{
    var timeElement = $(event.target);
    var div = timeElement.closest('div.form-group');
    var errorDiv = timeElement.next('.validation-error');
    if (! errorDiv.length) {
       // console.log("what the fuck?");
        timeElement.after($("<div>").addClass('alert alert-warning validation-error'));
        errorDiv = timeElement.next('.validation-error');
    }
    var time = timeElement.val().trim();
    if ("" === time) {
        return;
    }
    var re = /^(0?[1-9]|1[0-2]):?([0-5]\d)? *((a|p)m?)?$/i;
    var hour, minute, ap;
    matches = time.match(re);
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
            ap = ap + 'm';
        }
    } else if (matches = time.match(/^([01][0-9]|2[1-3])([0-5][0-9])$/)) {
        hour = matches[1];
        ap = 'am';
        if (hour > 12) {
            hour -= 12;
            ap = 'pm';
        }
        minute = matches[2];
    } else {
        errorDiv.addClass("alert alert-warning validation-error").text("invalid time").show();
        //div.addClass('has-error has-feedback');
        return;
    }
    div.removeClass("alert alert-warning validation-error");
    errorDiv.empty().hide();
    timeElement.val((hour + ":" + minute + " " + ap).toLowerCase());
};

formatDocketElement = function(event)
{
    element = $(event.target);
    var div = element.closest('div.form-group');
    var errorDiv = element.next('.validation-error');
    if (! errorDiv.length) {
        // try something else
        errorDiv = $('#docket').parent().next('.validation-error');
        if (! errorDiv.length)  {
            // last resort
            element.after($("<div>").addClass('alert alert-warning validation-error'));
            errorDiv = element.next('.validation-error');
        }
    }
    element.val(element.val().trim());
    if (! element[0].value ) {
        errorDiv.empty().hide();
        element.data('valid',1);
        return element;
    }
    matches = element[0].value.match(DocketRegExp);
    if (element[0].value && ! matches) {
        errorDiv.text("invalid docket number").show().trigger("show");
        element.data('valid',0);
        return div.addClass('has-error has-feedback');

    } else {
        div.removeClass('has-error has-feedback');
        var year = matches[1];
        var flavor = matches[2];
        var number = matches[3];
    }
    if (year.length === 2) {
        year = year <= 50 ? "20"+year : "19"+year;
    }
    flavor = flavor.toUpperCase();
    if (-1 !== flavor.indexOf('CR')) {
        flavor = 'CR';
    } else if (flavor[0] === 'M') {
        flavor = 'MAG';
    } else {
        flavor = 'CIV';
    }
    if (number.length < 4) {
        var padding = new Array(5 - number.length).join("0");
        number = padding + number;
    } else if (number.length === 5) {
        // four digits with up to three leading zeroes is enough
        number = number.replace(/^00/,"0");
    }
    element.val(year + '-'  + flavor + '-' + number)
            .data('valid',1);
    errorDiv.empty().hide();
    return element;
};
