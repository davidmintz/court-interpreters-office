/* global displayValidationErrors, moment, tui $ */

//08:30:00.000000

// const { data } = require("jquery");

var dp_defaults = {
    // dateFormat:"yy-mm-dd",
    showOtherMonths : true,
    selectOtherMonths : true,
    changeMonth : true,
    changeYear : true
};

const non_spanish_event_report = function(result, table)
{
    
    var keys = ["date","judge","interpreter","language","rating","type","docket"];
    var head = keys.map(i=>`<th>${i}</th>`).join("");
    table.children("thead").html(`<tr>${head}</tr>`);
    var data = result.data;
    var rows = [];
    Object.keys(data).forEach(r=>{
       
        var obj = moment(data[r].date.date);
        console.log(obj.format("YYYY-MM-DD"));
        data[r].date = obj.format("DD-MMM-YYYY");
        // data[r].time = "shit";        
        var row = $("<tr></tr>");
        row.append(keys.map(i=>`<td>${data[r][i]}</td>`).join(""));
        if (parseInt(data[r].cancelled)) {
            row.addClass("event-cancelled");
        }
        row.children().last().addClass("text-nowrap");
        row.children().first().addClass("text-nowrap");
        rows.push(row);
    });
    table.children("tbody").html(rows);
    if (result.pages) {
        var pages = result.pages;
        $("p.pagination-head").text(`Displaying ${pages.firstItemNumber} through ${pages.lastItemNumber} of ${pages.totalItemCount}`);
        var list = $("nav.pagination ul");
        list.empty();
       
        /*<ul class="pagination mb-0">
<li class="disabled"><span class="page-link">&lt;</span></li>
        <li class="page-item active"><span class="page-link">1</span></li>
            <li class="page-item"><a class="page-link" href="/admin/search?page=2">2</a></li>
           ...
            <li class="page-item"><a class="page-link" href="/admin/search?page=9">9</a></li>
            <li class="page-item"><a class="page-link" href="/admin/search?page=10">10</a></li>
    <!-- Next page link -->
  <li class="page-item"><a class="page-link" href="/admin/search?page=2">&gt;</a></li>
<li class="page-item"><a class="page-link" title="last page of results" href="/admin/search?page=12">&gt;&gt;</a></li>
</ul>*/
        if (pages.pageCount < 2) {
            return;
        }
        var links = Object.keys(pages.pagesInRange).map(i=>{
            var li =  $("<li class=\"page-item\"></li>");
            if (i == pages.current) { li.addClass("active"); }
            return li.append(`<a data-page="${i}" href="#" class="page-link">${i}</a>`);
        });
        if (pages.firstPageInRange > 1) {
            links.unshift($("<li class=\"page-item\"></li>").append(`<a class="page-link" data-page="${1}" href="#">&lt;</a>`));
        }
        links.push($("<li class=\"page-item\"></li>").append(`<a class="page-link" data-page="${pages.current + 1}" href="#">&gt;</a>`));
        if (pages.pageCount > pages.lastPageInRange) {
            var li = $("<li class=\"page-item\"></li>").append(`<a href="#" data-page="${pages.lastPageInRange + 1}" class="page-link">&gt;&gt;</a>`);
            links.push(li);
        }
        list.append(links);        
    }

    /*
        current: 1
        currentItemCount: 20
        first: 1
        firstItemNumber: 1
        firstPageInRange: 1
        itemCountPerPage: 20
        last: 12
        lastItemNumber: 20
        lastPageInRange: 10
        next: 2
        pageCount: 12
    */


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
        "<tr><th class=\"text-left\">interpreter</th><th>language</th><th class=\"text-right\">no. events</th></tr>"
    );
    var rows = [];
    
    report_data.forEach(r=>{
        var row = $("<tr></tr>");
        row.append(
            `<td>${r.interpreter}</td><td>${r.language}</td><td class="text-right">${r.events}</td>`   
        );
        rows.push(row);
    });
    table.children("tbody").html(rows);
};
const sort_by_pct = function(e){
    var th = $(e.target);
    var order = th.data("order")||"ASC";
    console.log(`sort ${order}`);
    var tbody = th.closest("thead").next("tbody");
    var rows = tbody.children("tr");
    console.log(`sorting ${rows.length} tr elements`);
    var sorted = rows.sort((a,b)=>{
        var i = parseInt($(a).children("td").last().text().trim());
        var j = parseInt($(b).children("td").last().text().trim());
        if (order === "ASC") {
            if (i > j) { return 1; }
            if (j < i) { return -1; }
            return 0;
        } else { // desc
            if (i < j) { return 1; }
            if (j > i) { return -1; }
            return 0;
        }
    });
    tbody.html(sorted);
    // flip the sort order
    th.data({order: order === "ASC" ? "DESC" : "ASC"});
};
const table_belated_in_court_request = function(report_data, table){
    
    table.children("thead").html(
        `<tr>
            <th class="text-left">judge</th><th class="text-right">&lt;1 day</th><th class="text-right">&lt;2 days</th><th class="text-right">total</th><th data-order="" style="cursor:pointer" class="text-right">% belated <span class="fa fa-sort"></th>
        </tr>`
    );
    var rows = [];
    var totals = {
        sub_1: 0, sub_2 : 0, total: 0
    };
    report_data.forEach(r=>{
        var row = $("<tr></tr>");
        var pct = ((parseInt(r.sub_1)+parseInt(r.sub_2))/r.total *100).toFixed(2);
        row.append(`<td>${r.judge}</td><td class="text-right">${r.sub_1}</td><td class="text-right pr-2">${r.sub_2}</td><td class="text-right pr-2">${r.total}</td><td data-order="" class="text-right">${pct}</span></td>`);
        rows.push(row);
        totals.sub_1 += parseInt(r.sub_1);
        totals.sub_2 += parseInt(r.sub_2);
        totals.total += parseInt(r.total);
    });
    var footer = table.children("tfoot").css({borderTop:"2px solid gray"});
    var avg = ((totals.sub_1 + totals.sub_2)/totals.total*100).toFixed(2);    
    table.children("tbody").html(rows);
    footer.html(
        `<tr>
            <td>TOTAL ${rows.length} judges</td>
            <td class="text-right">${totals.sub_1}</td>
            <td class="text-right">${totals.sub_2}</td>
            <td class="text-right">${totals.total}</td>
            <td class="text-right">${avg}</td>
        </tr>`);    
    var th = table.find("th").last();
    th.on("click",sort_by_pct);
    
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
    $("ul#tabs").show();
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

    $("nav.pagination").on("click","li",function(e){
        e.preventDefault();
        var page = $(this).children("a").data("page");
        $("input[name='page']").val(page);
        btn.trigger("click");
    });

    btn.on("click", function (e) {
        e.preventDefault();
        // $("nav.pagination, p.pagination-head").empty();
        btn.html("<span class='fa fa-cog fa-spin'></span");
        var table = $("#table > table"); 
        table.children("tfoot").empty();
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
                    table_interpreter_usage_by_language(data,table);
                    chart_interpreter_usage_by_language(data);
                    $("ul#tabs").show();
                    break;
                case "interpreter usage by interpreter":
                    table_interpreter_usage_by_interpreter(data,table);
                    $("ul#tabs").hide();
                    break;
                case "belated in-court requests per judge":
                    table_belated_in_court_request(data,table);
                    $("ul#tabs").hide();
                    break;
                case "non-Spanish events including language credential":
                    non_spanish_event_report(res.result, table);
                    break;
                default:
                    alert("sorry, this one is not yet implemented");
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