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
            // find first day of current quarter, shouldn't be so hard )-:
            var q_month, current_month = today.month();
            if (current_month <= 2) { 
                q_month = "01" ;
            } else if (current_month <= 5) { 
                q_month = "04" ;
            } else if (current_month <= 8) { 
                q_month = "07" ;
            } else {
                q_month = "10";
            }
            from = moment(`${today.year()}-${q_month}-01`);
            to = today;
            element_from.val(from.format("MM/DD/YYYY"));
            element_to.val(to.format("MM/DD/YYYY"));
            
            break;
        case "PY":
            break;
        case "PQ":
            var pq_month; current_month = today.month();
            if (current_month <= 2) { 
                pq_month = "10" ;
            } else if (current_month <= 5) { 
                pq_month = "01" ;
            } else if (current_month <= 8) { 
                pq_month = "04" ;
            } else {
                pq_month = "07";
            }
            from = moment(`${today.year()}-${pq_month}-01`);
            element_from.val(from.format("MM/DD/YYYY"));
            to = moment(`${today.year()}-${pq_month}-01`).add(3,"months")
                .subtract(1,"day");
            element_to.val(to.format("MM/DD/YYYY"));
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