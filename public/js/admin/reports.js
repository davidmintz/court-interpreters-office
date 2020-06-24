/* global displayValidationErrors, moment, tui $ */

var dp_defaults = {
    // dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true
};

const table_interpreter_usage_by_language = function(data, table) {
    table.children("thead").html(
        "<tr><th class=\"text-left\">language</th><th class=\"text-right\">in-court</th><th class=\"text-right\">ex-court</th><th class=\"text-right\">other</th><th class=\"text-right\">cancelled</th><th class=\"text-right\">total</th></tr>"
    );
    var rows = [];
    var totals = {in: 0, out: 0, cancelled : 0, languages: 0, other: 0, total: 0 };
    data.forEach(r=>{
        var row = $("<tr></tr>");
        row.append(
            `<td>${r.language}</td><td class="text-right">${r.in_court}</td><td class="text-right">${r.ex_court}</td><td class="text-right">${r.total - r.in_court - r.ex_court}</td><td class="text-right">${r.cancelled}</td><td class="text-right">${r.total}</td>`   
        );
        rows.push(row);
        totals.in += parseInt(r.in_court);
        totals.out += parseInt(r.ex_court);
        totals.total += parseInt(r.total);
        totals.cancelled += parseInt(r.cancelled);
    });
    rows.push($("<tr></tr>").html(`<td>TOTAL ${rows.length} languages</td>
    <td class="text-right">${totals.in}</td>
    <td class="text-right">${totals.out}</td>
    <td class="text-right">${totals.total - totals.in - totals.out}</td>
    <td class="text-right">${totals.cancelled}</td>
    <td class="text-right">${totals.total}</td>`));
    table.children("tbody").html(rows);    
};
const table_interpreter_usage_by_interpreter = (report_data,table)=>
{
    table.children("thead").html(
        "<tr><th class=\"text-left\">interpreter</th><th class=\"text-right\">language</th><th class=\"text-right\">no. events</th></tr>"
    );
    var rows = [];
    report_data.forEach(r=>{
        var row = $("<tr></tr>");
        row.append(
            `<td>${r.interpreter}</td><td class="text-right">${r.language}</td><td class="text-right">${r.events}</td>`   
        );
        rows.push(row);
    });
    table.children("tbody").html(rows);

};
const chart_interpreter_usage_by_language = (report_data) => {
    console.log(`languages: ${report_data.length}`);
    var max = Math.max(...report_data.map(x=>parseInt(x.total)));
    console.log(`setting max = ${parseInt(max + (max/10))}`);
    var container = document.getElementById("chart");
    $(container).empty();
    var data = {
        categories : report_data.map(x=>x.language),
        series: [
            { name : "total events", data : report_data.map(x=>x.total)
            },
            {
                name : "cancelled events",
                data : report_data.map(x=>x.cancelled)
            }
        ]
    };
    var options = {
        chart: {
            width: 1000,
            height: report_data.length*75,
            // title: "usage by language",
            format: "1,000"
        },
        chartExportMenu : {
            // thank you, source code
            visible : false
        },
        yAxis: {
            title: "language"
        },
        xAxis: {
            title: "events",
            min: 0,
            max: parseInt(max + (max/10))
            // suffix: "$"
        },
        series: {
            showLabel: true
        }
    };
    tui.chart.barChart(container, data, options);
    // crude hack till we learn better
    $("text").css({fontSize: "13px"});
};

$(function () {
    var btn = $("#btn-run");
    var form = btn.closest("form");
    var today = moment();
    var date_range = $("#date-range");
    date_range.on("change",function(){
        var to, from;
        var from_el = $("#date-from");
        var to_el = $("#date-to");
        switch (date_range.val()) {
        case "YTD":
            to = today;
            from = moment([today.year()]);           
            break;
        case "QTD":
            switch (today.quarter()) {
            case 1:
                from = moment([today.year()]); 
                break;
            case 2:
                from = moment([today.year(),3]); 
                break;
            case 3:
                from = moment([today.year(),6]);
                break;
            case 4: 
                from = moment([today.year(),9]);                
            }            
            to = today;
            break;
        case "PY":
            var YYYY = today.year() - 1;
            from = moment([YYYY]);
            to = moment(from).add(1,"year").subtract(1,"day");
            break;
        case "PQ":
            YYYY = today.year();
            switch (today.quarter()) {
            case 1:
                from = moment([YYYY - 1, 9]);
                break;
            case 2:
                from = moment([YYYY]);
                break;
            case 3:
                from = moment([YYYY,3]);
                break;
            case 4:
                from = moment([YYYY,6]);
                break;                    
            }
            to = moment(from).add(3,"month").subtract(1, "day");
            break;
        case "FYTD":
            if (today.month() >= 9) {
                from = moment([today.year(),9]);                
            } else {
                from = moment([today.year()-1,9]);
            }
            to = today;           
            break;
        case "PFY":
            if (today.month() >= 9) {
                from = moment([today.year()-1,9]);
            } else {
                from = moment([today.year()-2,9]);
            }
            to = moment(from).add(1, "year").subtract(1,"day");
            break;
        case "CUSTOM":
            to_el.val("").removeAttr("readonly");
            from_el.val("").removeAttr("readonly")[0].focus();            
            break;
        default:
            break;
        }
        if (from && to) {
            from_el.val(from.format("MM/DD/YYYY")).attr({readonly:true});
            to_el.val(to.format("MM/DD/YYYY")).attr({readonly:true});
        }
    });
    // initialize
    if (date_range.val() !== "CUSTOM") {
        date_range.trigger("change");
    }
    $("input.date").datepicker(dp_defaults);
    btn.on("click", function (e) {
        e.preventDefault();
        btn.html("<span class='fa fa-cog fa-spin'></span");      
        var params = form.serialize();
        $.get(form.attr("action"), params)
            .then(res => {
                btn.html("run");  
                if (res.validation_errors) {
                    return displayValidationErrors(res.validation_errors);
                }
                $("form .validation-error").hide();
                var data = res.result.data;               
                switch (res.result.report_type) {
                case "interpreter usage by language":
                    table_interpreter_usage_by_language(data,$("#table > table"));
                    chart_interpreter_usage_by_language(data);
                    break;
                case "interpreter usage by interpreter":
                    table_interpreter_usage_by_interpreter(data,$("#table > table"));
                    $("#chart").html(
                        "<p style='max-width:300px' class='p-2 border border-warning rounded mx-auto shadow-sm mt-3'>chart is not yet implemented</p>"
                    );
                    break;
                }

            });
    });
});
/*
    var container = document.getElementById("result");
    var data = {
        categories: ["June", "July", "Aug", "Sep", "Oct", "Nov"],
        series: [
            {
                name: "Budget",
                data: [5000, 3000, 5000, 7000, 6000, 4000]
            },
            {
                name: "Income",
                data: [8000, 1000, 7000, 2000, 5000, 3000]
            }
        ]
    };
    var options = {
        chart: {
            width: 1000,
            height: 650,
            title: "Monthly Revenue",
            format: "1,000"
        },
        yAxis: {
            title: "Month"
        },
        xAxis: {
            title: "Amount",
            min: 0,
            max: 9000,
            suffix: "$"
        },
        series: {
            showLabel: true
        }
    };

    tui.chart.barChart(container, data, options);

*/