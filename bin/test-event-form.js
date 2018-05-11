/**
 casperjs test for admin/schedule/add
 */
//var baseUrl = "https://office.localhost";
//
var casper, moment, $; // make eslint happy
//logLevel: 'debug'
casper.options.logLevel = "info";
casper.options.verbose = false;

casper.test.begin("Authenticate and Add Event", function suite(test)
{
    casper.start("https://office.localhost/", function() {
        test.assertTitleMatches(/Court Interpreters Office/i,"main page title looks good");
        this.clickLabel("log in");
    });
    casper.log("shit is running","info");
    casper.then(function() {
        test.assertTitleMatches(/user login/,"login page is accessible, title looks good");

        this.fillSelectors("form", {
            "#identity":    "david",
            "#password":    "boink"
        }, true);
        this.waitForUrl("https://office.localhost/admin");
    });

    casper.thenOpen("https://office.localhost/admin/schedule/add",
        function()
        {
            test.assertTitleMatches(/admin.+schedule.+add/,"requesting admin/schedule/add title looks good");
            test.assertHttpStatus(200, "requesting admin/scheduled/add is status code 200");
            test.assertTitleMatches(/admin/, "title contains admin");
            test.assertExists("#event-form", "element #event-form exists");
            test.assertExists("#time","a time field exists");
            this.sendKeys("#time","330");
            this.waitFor(function check(){
                return this.evaluate(function(){return $("#time").val() != "330";});
            });
            var event_time = this.evaluate(function(){return $("#time").val();});
            test.assertEquals(event_time,"3:30 pm","time field is automatically formatted as expected");
            this.click("#date");
            this.waitUntilVisible("#ui-datepicker-div");
            test.assertExists("td a.ui-state-highlight","highlighted date td cell exists");
            this.click("td a.ui-state-highlight");
            this.waitWhileVisible("#ui-datepicker-div");
            var event_date = this.getFormValues("#event-form")["event[date]"];
            var expected = this.evaluate(function(){
                return moment().format("MM/DD/YYYY");
            });
            test.assertEquals(event_date, expected, "today's date is in the date field");

        });
    /**
     * set the event type and judge, and test automatic setting
     * of judge's default courthouse and courtroom. test automatic
     * docket formatting
     */
    casper.then(function(){

        test.assertExists("#event-type","event-type element exists");
        var judge_name = this.evaluate(
            function(){return $("#judge option:contains('Forrest')").text();}
        );
        test.assert(typeof judge_name === "string" && judge_name.length > 0,
            "judge name was found: "+judge_name);
        var sentence = this.evaluate(
            function(){ return $("#event-type option:contains('sentence')").text(); }
        );
        test.assert(sentence === "sentence","event-type option \"sentence\" was found");
        var initial_location_count = this.evaluate(
            function() { return $("#location option").length; }
        );
        test.assertEquals(1,initial_location_count,"there is initially only one option in location select");

        this.fillSelectors(
            "#event-form",{
                "#event-type":sentence,
                "#judge": judge_name
            }
        );
        test.assertExists("#docket","docket element exists");
        this.sendKeys("#docket","18cr4321");
        this.waitFor(function(){
            return this.evaluate(function(){return $("#docket").val() != "18cr4321";});
        });
        var docket = this.getFormValues("#event-form")["event[docket]"];
        test.assertEquals(docket,"2018-CR-4321","docket is automatically formatted as expected");
        test.assertExists("#location","location element exists");
        test.assertExists("#parent_location","parent location element exists");
        this.waitFor(function(){
            return this.evaluate(function(){return $("#parent_location").val() != "";});
        });
        var courthouse = this.evaluate(function(){
            return $("#parent_location option:selected").text();
        });
        test.assertEquals(courthouse,"500 Pearl","courthouse is automatically selected when a judge and in-court event are set");
        this.waitFor(function check()  {
            return this.evaluate(function(){
                return $("#location option").length > 10;
            });
        },function then() {
            var location_option_count = this.evaluate(function(){
                return $("#location option").length;
            });
            test.assert(location_option_count > 10,
                "there are now more than 10 location options");
            var courtroom = this.evaluate(function(){
                return $("#location option:selected").text();
            });
            // courtroom names look like "23B" or " 706"
            var re = /^\d{2}(?:[A-D]|\d)$/;
            test.assert( 1 === courtroom.match(re).length,
                "courtroom is automatically selected when judge and in-court event are set");
        });
    });

    /**
     * test setting language to Spanish and assignment of an interpreter
     */
    casper.then(function setLanguageAndAssignAnInterpreter() {

        test.assertExists("#language");
        var initial_interpreter_count = this.evaluate(function(){
            return $("#interpreter-select option").length;
        });
        test.assert(initial_interpreter_count === 1,"no interpreter options initially set");

        this.fillSelectors(
            "#event-form", {"#language":"Spanish" },false
        );
        // sanity check
        var assigned = this.evaluate(
            function(){ return $("li.interpreter-assigned").length; }
        );
        test.assert(0 === assigned, "no interpreter assigned as yet");
        this.waitFor(function check(){
            return this.evaluate(function() {
                return $("#interpreter-select option").length > 10;}
            );
        })
        .then(function(){
            var spanish_interpreter_count = this.evaluate(
                function(){ return $("#interpreter-select option[value!='']").length; }
            );
            test.assert(spanish_interpreter_count > 10,
                "after setting language, >10 Spanish interpreter options are in "
                + "the select menu (total is "+spanish_interpreter_count+")");
        })
        .then(function(){
            this.fillSelectors(
                "#event-form", {"#interpreter-select":"Mintz, David" },false
            );
            this.click("#btn-add-interpreter");
            this.waitFor(
                function(){
                    return this.evaluate(function(){
                        return $("li.interpreter-assigned").length;
                    });
                }
            )
        .then(function(){
                var text = this.fetchText("li.interpreter-assigned").trim();
                var name = "Mintz, David";
                test.assert(0 === text.indexOf(name),
                    "element li.interpreter-assigned contains string \""+name+"\"");
            });
        });

        /** test adding a defendant name */
        casper.then(function addDefendantName(){

            test.assertExists("#defendant-search","there is a defendant-search element");
            this.sendKeys("#event-form #defendant-search","rodr, j", {keepFocus: true});
            this.waitUntilVisible("ul.ui-autocomplete")
            .then(function(){
                this.click("ul.ui-autocomplete li:nth-child(3)");
            })
            .then(function(){
                this.waitUntilVisible("#defendant-names li.defendant");
            })
            .then(function(){
                var name = this.fetchText("#defendant-names li.defendant span:first-of-type").trim();
                test.assert(name.match(/^rodr.+, j.+/i) !== null,
                    "a defendant-name li element has been added and its text ("
                    + name + ") matches search input \"rodr, j\""
                );
            });
        });

        /** test selecting a submitter "hat" and an individual person */

        casper.then(

            function(){
                this.fillSelectors("#event-form",{
                    "#hat":"Courtroom Deputy"
                });

                this.waitFor(
                    function(){
                        return this.evaluate(function(){
                            return $("#submitter option").length > 10;
                        });
                    }
                ).then(
                    function(){
                        var submitter_count = this.evaluate(
                            function(){ return $("#submitter option[value!='']").length; }
                        );
                        test.assert(submitter_count > 10,
                            ">10 options in #submitter menu (total "+submitter_count+")")
                    }

                );
            }
        );

    });

    casper.run(function() {
        test.done();
        /*require('utils').dump(this.result.log);*/
    });
});
