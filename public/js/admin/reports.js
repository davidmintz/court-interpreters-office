/* global displayValidationErrors, moment, $ */
$(function () {
    var btn = $("#btn-run");
    var form = btn.closest("form");
    var today = moment();
    var date_range = $("#date-range");
    var element_from = $("#date-from");
    var element_to = $("#date-to");
    date_range.on("change",function(){
        var to, from;
        switch (date_range.val()) {
        case "YTD":
            to = today;
            from = moment(`${today.year()}-01-01`);
            // just for kicks, for now...
            element_from.val(from.format("MM/DD/YYYY"));
            element_to.val(to.format("MM/DD/YYYY"));
            break;
        case "QTD":
            var q = parseInt(today.month()/4) + 1;
            console.log(`current quarter seems to be: ${q}`);
            break;
        case "PY":
            break;
        case "PQ":
            break;
        case "FYTD":
            break;
        case "PFY":
            break;
        case "CUSTOM":
            break;
        default:
            break;
        }
    });
/* for (i = 0; i < 12; i++) {
   
  // console.log(`month: ${i}; over 4 rounded +1 ${parseInt(i/4) +1} modulus ${i % 4}`);
	if (i <= 2) { Q = 1 } else if (i <= 5) { Q = 2} else if ( i <= 8) { Q = 3} else { Q = 4 ;}
	console.log(`m ${i} is in Q ${Q}`);*/
    btn.on("click", function (e) {
        e.preventDefault();
        var params = form.serialize();
        $.get(form.attr("action"), params)
            .then(res => {
                if (res.validation_errors) {
                    return displayValidationErrors(res.validation_errors);
                }
                console.log(res);
            });
    });
});