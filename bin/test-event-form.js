/*
var casper = require('casper').create({
    verbose:true,
    logLevel:'info'
});

"public/js/lib/jquery.min.js"
"public/js/lib/bootstrap/bootstrap.bundle.js"
"public/js/common.js"
"public/js/lib/jquery-ui/jquery-ui.js"
"public/js/lib/moment/min/moment.min.js"
"public/js/event-form.js"
*/
var baseUrl = 'https://office.localhost';
var cookieJar = 'cookies.json';
casper.options.clientScripts.push("public/js/lib/moment/min/moment.min.js");
var moment;
casper.test.begin(
    "Authenticate and Add Event",  function suite(test) {
        casper.start('https://office.localhost/', function() {
            test.assertTitleMatches(/Court Interpreters Office/i,"main page title looks good");
            this.clickLabel('log in');
        });

        casper.then(function() {
            test.assertTitleMatches(/user login/,"login page is accessible, title looks good");

            this.fillSelectors('form', {
                '#identity':    'david',
                '#password':    'boink'
            }, true);
            this.waitForUrl("https://office.localhost/admin");

        });
        ///*
        casper.thenOpen(
            'https://office.localhost/admin/schedule/add',
            function(){
                test.assertTitleMatches(/admin.+schedule.+add/,"requesting admin/schedule/add title looks good");
                test.assertHttpStatus(200, "requesting admin/scheduled/add is status code 200");
                test.assertTitleMatches(/admin/, "title contains admin");
                test.assertExists("#event-form");

                test.assertExists("#event-type");
                test.assertExists("#event-type > option:nth-child(4)");
                var label = this.fetchText("#event-type > option:nth-child(4)");
                test.assertEquals(label,"arraignment","fourth event-type option is arraignment");
                //this.click("#event-type > option:nth-child(4)");
                this.fillSelectors(
                    "#event-form",{
                        "#event-type":"arraignment"
                    }
                );
                this.click("#date",function(){this.echo("we clicked the fucking link!")});
                this.waitUntilVisible("#ui-datepicker-div",function(){
                    this.echo("datepicker is now visible!");
                });
                test.assertExists("td a.ui-state-highlight","highlighted date td cell exists");
                this.click("td a.ui-state-highlight");
                this.echo("we clicked the fucking link to today's date!");
                this.waitWhileVisible("#ui-datepicker-div",function(){
                this.echo("datepicker is no longer visible");
                //test.assertField("#date","05/09/2018");
                var event_date = this.getFormValues("#event-form")["event[date]"];
                //var dateObj = new Date();
                //var today = [dateObj.getMonth()+1,dateObj.getDate(),dateObj.getFullYear()].join("/");
                var today = moment().format("MM/DD/YYYY");
                //test.assertEquals(event_date,today,"today's date was selected with the datepicker");
            });

                /*
                this.waitFor(function check(){
                    return this.evaluate(function() {
                        return $("#event-type").val();
                    });
                });
                */

            }
        );

        casper.run(function() {
            test.done();
        });
    }

);

var login = function(){
    //this.log("attempting login",'warning');
    this.fillSelectors('form', {
        '#identity':    'david',
        '#password':    'boink'
    },true);
};
/*
casper.start(baseUrl + '/login',function(){
    this.log("attempting login",'warning');
    this.fillSelectors('form', {
        '#identity':    'david',
        '#password':    'boink'
    }, true);
});
casper.thenOpen(baseUrl+"/schedule/add");

casper.run();
*/
