

//const chai = require('chai'), expect = chai.expect, should = chai.should();
const assert = require("assert");

const Browser = require("zombie");
const moment = require("moment");

Browser.localhost("office.localhost");

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
        it("should display schedule page",function(done){
            browser.assert.success();
            browser.assert.element("h2.navigation");
            done();
        });
        it("should have a right-arrow",function(){
            browser.assert.element("a.fa-arrow-right");
            //console.log(`location is now: ${browser.location.href}`);
            return browser.fire("a.fa-arrow-right","click");
        });
        it("clicking right arrow should advance schedule by a day",function(done){
            browser.assert.status(200);
            var date = moment(new Date());
            var increment, dayOfWeek = date.format("d");
            switch (dayOfWeek) {
                case "5":
                    increment = 3;
                    break;
                case "6":
                    increment = 2;
                    break;
                default:
                    increment = 1;
            }
            var str = date.add(increment,"days").format("YYYY/MM/DD");
            assert.strictEqual(`/admin/schedule/${str}`,browser.location.pathname);
            browser.assert.element("h2 small.text-muted");
            var expected = date.format("ddd DD MMM YYYY");
            browser.assert.text("h2 small.text-muted",expected);
            done();
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
   after(function(){ return browser.visit("/")});
});
