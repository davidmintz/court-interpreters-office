/*
var casper = require('casper').create({
    verbose:true,
    logLevel:'info'
});

*/
var baseUrl = 'https://office.localhost';
var cookieJar = 'cookies.json';
casper.test.begin(
    "Authentication",  function suite(test) {
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
                
            }
        );

        casper.run(function() {
            test.done();
        });
    }

);

var login = function(){
    this.log("attempting login",'warning');
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
