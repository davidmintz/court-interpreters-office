casper.options.logLevel = "info";
casper.options.verbose = false;
casper.options.timeout = 2500;
casper.options.viewpostSize = {width:1600,height:1200};


casper.test.begin("User Registration", function suite(test)
{
    casper.start("https://office.localhost/user/register", function() {
        test.assertTitleMatches(/create.+account/i,
            "registration page title looks good");
        test.assertExists("#registration-form","registration form exists");
        test.assertExists("#fieldset-personal-data","contact data fieldset exists");
        test.assertVisible("#fieldset-personal-data","contact data fieldset is visible");
        test.assertExists("#fieldset-hat");
        test.assertNotVisible("#fieldset-hat");
        this.fillSelectors("#registration-form",{
            "#lastname":"Wanker",
            "#firstname":"Boink",
            "#office_phone":"2128054321"
        });
        casper.capture("WTF-0.png");
        casper.echo("took a picture of first carousel slide/fieldset");
        this.clickLabel("next");
        this.waitUntilVisible("#fieldset-password");
        //this.waitUntilVisible(".validation-error");


        casper.then(function(){
            this.sendKeys("#email","boinker_fuckwanker@nysd.uscourts.gov");
            this.sendKeys("#password","fuckyou!");
            this.sendKeys("#password-confirm","fuckyou!");

            this.clickLabel("next");
            this.evaluate(function(){
                $(".carousel").carousel("next");                
            })
            //this.echo(shit);
            this.wait(1000,function(){
                casper.capture("WTF-1.png");
                casper.echo("took a picture of password fieldset");
            }).then(function(){
                this.clickLabel("back");
            });
            this.waitUntilVisible("#fieldset-personal-data")
            .then(function(){
                    casper.capture("WTF-2.png");
                    casper.echo("took a picture of preceding fieldset");

            });
            //test.assertExists("#fieldset-hat","shit exists");
            //this.waitUntilVisible("#fieldset-hat");
        });
    });
    casper.log("shit is running","info");
    /*
    casper.then(function() {
        test.assertTitleMatches(/user login/,"login page is accessible, title looks good");
        this.fillSelectors("form", {
            "#identity":    "david",
            "#password":    "boink"
        }, true);
        this.waitForUrl("https://office.localhost/admin");
    });
    */
    casper.run(function() {
        test.done();
        /*require('utils').dump(this.result.log);*/
    });
});
