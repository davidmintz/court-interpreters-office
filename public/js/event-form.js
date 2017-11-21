/**
 * public/js/event-form.js
 */

moment = window.moment; 
Modernizr = window.Modernizr;

$(document).ready(function()
{
    if (! Modernizr.inputtypes.date) {
        $('input.date').datepicker({
            changeMonth: true,
            changeYear: true
        });
        $('input.date').each(function(i,element){
            if (element.value.match(/^\d{4}-\d\d-\d\d$/)) {
                element.value = element.value.replace(/(\d{4})-(\d\d)-(\d\d)/,"$2/$3/$1");
            }
        });
    } else { console.log("date element is supported!");}
    if (! Modernizr.inputtypes.time) {
        $("input.time")
          .each(function(){formatTimeElement($(this));})
          .on("change",parseTime);
    } else {console.log( "time element is supported!"); }
    $('input.docket').on("change",formatDocketElement);
    
    var parentLocationElement = $('#parent_location');
    var locationElement = $('#location');
    
    parentLocationElement.on("change",function() {
        if (! parentLocationElement.val()) {
            locationElement.attr({disabled : "disabled"});                        
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
                    locationElement.children().not(":first").remove();
                    locationElement.append(options)
                            .trigger("sdny.location-update-complete");                        
            });
        }
    });
    
    if (! parentLocationElement.val()){
        locationElement.val("").attr({disabled : "disabled"});
    } else {
        //parentLocationElement.trigger("change");
    }
    /** this applies to admin form, not "request" form **/
    var languageElement = $('#language');
    var interpreterSelectElement = $("#interpreter-select");
    
    if (! languageElement.val()) {
        interpreterSelectElement.attr("disabled","disabled");
    }
    
    // (re)populate the interpreter select element according to the language
    languageElement.on('change',function(event,params){
        
        var language_id =  languageElement.val();
        // remove the interpreters if the language changes, except
        // when we're initially triggered on page load, which we 
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
    }).trigger("change",{remove_existing:false});
    
    // add an interpreter to this event
    interpreterSelectElement.on('change',function(){
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
    
    hatElement = $('#hat');    
    submitterElement = $('#submitter');
    
    if (!hatElement.val()) {
        submitterElement.attr({disabled:"disabled"});
    }
    //
    var judgeElement = $('#judge');
    var anon_judge = $('#is_anonymous_judge');
    judgeElement.on('change',function(){
         anon_judge.val(
            judgeElement.children(':selected').data('pseudojudge') ? 1 : 0
        );
    }).trigger('change');
    //
    hatElement.on("change",function()
    {
        console.warn("shit changes");
        var hat_id = hatElement.val();
        if (! hat_id) {
            hatElement.children().not(":first").remove();
            return;
        }

         $.getJSON('/admin/people/get',
                { hat_id: hat_id },
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
        
    });
    
    $("#event-form").on("submit",function(e){
        if (! locationElement.val()) {
            // no specific location selected, so the general location
            // should be submitted instead
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
    /** deft name autocompletion */
    $('#defendant-search').autocomplete(
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
                }
             }   
         );
    
    
    /* ==================== */
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
        timeElement.after($("<div>").addClass('validation-error'));
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
        errorDiv.text("invalid time format").show();
        div.addClass('has-error has-feedback');
        return;
    }
    div.removeClass('has-error has-feedback');
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
            element.after($("<div>").addClass('validation-error'));
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

