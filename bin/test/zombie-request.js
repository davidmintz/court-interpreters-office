

const chai = require('chai'), expect = chai.expect, should = chai.should();
const assert = require("assert");

const Browser = require("zombie");
const moment = require("moment");

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
            //console.log(browser.document.location.href);
        });
    });
    describe("display requests",function(){
        before(function(){
            return browser.visit("/requests/list");
        });
        it("should display shit",()=>{
            browser.assert.elements("tr",{atLeast: 5});
            var element = browser.querySelector("tbody > tr");
            var attribute = element.attributes.getNamedItem("data-request_id");
            id = attribute.value;
        });
    });

    describe("load event form",()=>{
        before(function(){
            return browser.visit(`/requests/update/${id}`);
        });
        it("should load an event form",()=>{
            //console.log(browser.document.location.href);
            browser.assert.element("#time");
            var time = browser.querySelector("#time").attributes.getNamedItem("value").value;
            console.log(`time is currently: ${time}`); // something like "4:00 pm"
        });
    });
});
