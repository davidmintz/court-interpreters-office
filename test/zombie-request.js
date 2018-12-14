

//const chai = require('chai'), expect = chai.expect, should = chai.should();
//const moment = require("moment");
const assert = require("assert");
const Browser = require("zombie");

var id;

Browser.localhost("office.localhost");

describe("Request update",function(){
    const browser = new Browser();
    before(function() {
        return browser.visit("/login");
    });

    describe("request user login",function(){
        before(function(){
            browser.fill("#identity","anthony_daniels@nysd.uscourts.gov");
            browser.fill("#password","boink");
            return browser.pressButton("log in");
        });
        it("should authenticate user anthony",function(){
            browser.assert.success();
            assert.ok(browser.document.title.indexOf("requests") !== -1);            
        });
    });
    describe("display requests",function(){
        before(function(){
            return browser.visit("/requests/list");
        });
        it("should display shit",function() {
            browser.assert.elements("tr",{atLeast: 5});
            var element = browser.querySelector("tbody > tr");
            var attribute = element.attributes.getNamedItem("data-request_id");
            id = attribute.value;
        });
    });

    describe("load event form and modify time",function() {
        before(function(){
            return browser.visit(`/requests/update/${id}`);
        });
        it("should load the event form",function() {
            browser.assert.success();
            browser.assert.url({ pathname: `/requests/update/${id}` });

        });
        it("should let us update time",function() {
            //console.log(browser.document.location.href);
            browser.assert.element("#time");
            var time = browser.querySelector("#time").attributes.getNamedItem("value").value;
            //console.log(`time is currently: ${time}`);
            var new_time = time === "4:00 pm" ? "11:00 am" : "4:00 pm";
            browser.fill("#time",new_time);
            return browser.pressButton("#btn-save");

        });
        it("should result in successful form submission",function(done){
            browser.assert.success();
            browser.assert.url({ pathname: '/requests/list' });
            browser.assert.element(".alert-success p");
            done();
        });

    });
    // apparently, if you have javascript running and running in the browser,
    // as with setTimeout(), it will hang
    after(function(){ // make it stop hanging!
        return browser.visit("/");

    });
});
