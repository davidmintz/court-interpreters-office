

const chai = require('chai'), expect = chai.expect, should = chai.should();
const assert = require("assert");

const Browser = require("zombie");

Browser.localhost("office.localhost", 80);

describe("admin test",function(){
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
            browser.assert.evaluate("$(\".navbar-right\").text().indexOf(\"welcome david\") !== -1");
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
        it("click right arrow should cause something to happen",function(){
            browser.fire("a.fa-arrow-right","click")
            .then(function(){
                console.log(`location is now: ${browser.location.href}`);
            })

        });

    })
    /*
    describe("loading event creation form",function(){
        before(function(){
            return browser.visit("/admin/schedule/add");
        });
        it("should display event form",function(){
            browser.assert.success();
        });
    })*/
});
