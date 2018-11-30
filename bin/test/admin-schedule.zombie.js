

const chai = require('chai'), expect = chai.expect, should = chai.should();
const assert = require("assert");

const Browser = require("zombie");
const moment = require("moment");

Browser.localhost("office.localhost", 80);

describe("admin schedule test",function(){
    const browser = new Browser();
    before(function() {
        return browser.visit("/login");
    });
    it("should load login page",function(){
        browser.assert.success();
    });
    describe("login should work",function(){
        before(function(){
            browser.fill("#identity","david");
            browser.fill("#password","boink");
            return browser.pressButton("log in");
        });
        it("should authenticate user david",function(){
            browser.assert.success();
            assert.ok(browser.document.title.indexOf("admin") !== -1);
            browser.assert.evaluate("$(\"nav div div:contains('welcome david')\").length");
        });
    });
    describe("loading current schedule",function(){
        before(function(){
            return browser.visit("/admin/schedule");
        });
        it("should display schedule page",function(){
            browser.assert.success();
        });
        it("should have a right-arrow",function(){
            browser.assert.element("a.fa-arrow-right");
        });
        it("clicking arrows should load next|previous day's schedule",
            function(){
            var today = moment(new Date());
            browser.fire("a.fa-arrow-right","click")
                .then(function(){
                //console.log(`location is now: ${browser.location.href}`);
                browser.assert.status(200);
                var dayOfWeek = today.format("d");
                var increment = dayOfWeek === "6" ? 2 : 1;
                var str = today.add(increment,"days").format("YYYY/MM/DD") + "$";
                browser.assert.url(new RegExp(str));
                browser.fire("a.fa-arrow-left","click")
                    .then((done)=>{
                        browser.assert.status(200);
                        browser.assert.element("h2 small.text-muted");
                        var str = moment().format("YYYY/MM/DD") + "$";
                        browser.assert.url(new RegExp(str))
                    });
            });
        });
    /*
    these things don't work because the previous callbacks have yet to finish.
     */
       //  it("should be able to continue",()=>{
       //      browser.visit("/admin/schedule/add")
       //      .then(()=>{
       //          browser.assert.status(200);
       //          browser.assert.url("/admin/schedule/add");
       //      }).catch((err)=>{console.log(err);});
       // });
    });
    /*
    describe("loading event creation form",function(){
        before(function(){
            return browser.visit("/admin/schedule/add");
        });
        it("should display event form",function(){
            browser.assert.success();
        });
    })
    */
});

/*
describe("loading event creation form",function(){
    const browser = new Browser();
    browser.debug();
    before(function(){
        return browser.visit("/login");
    });
    describe("shit",()=>{
        before(function(){
            browser.fill("#identity","david");
            browser.fill("#password","boink");
            return browser.pressButton("log in");
        });
    });


});
*/
