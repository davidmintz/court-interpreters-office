// https://mochajs.org/#getting-started
// http://zombie.js.org

// npm install mocha and zombie
// then:  node_modules/mocha/bin/mocha <path/to/>zombie.js
//


var chai = require('chai'), expect = chai.expect, should = chai.should();

//helper = require('./login_helper.js');


//var require, before, it, describe;

const Browser = require("zombie");
const assert = require("assert");


Browser.localhost("office.localhost", 80);

describe("example test",function(){
    const browser = new Browser();

    //helper.login(browser).then(console.log("and?"));

    before(function(done) {
        browser.visit("/", done);
    });
    it("should be successful", function() {
        browser.assert.success();
    });

    it("should see welcome page", function() {
        browser.assert.text("title", "Court Interpreters Office");
    });

    it("should have a login button", function() {
        // the example on their site had these calls chained but
        // that didn't work for us
        browser.assert.element("a:nth-child(1) > button:nth-child(1)");
        browser.assert.text("#login","log in");
        browser.assert.evaluate("$(\"#login\").length", 1);
        browser.assert.evaluate("$(\"#login\").attr(\"href\")", "/login");
    });

    describe("navigate to login",function(){
        before(function(done) {
            browser.visit("/login", done);
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
    });
    /*
    describe("try to do more shit?",function(){
        it("should fucking work",function(){
            browser.pressButton("a:nth-child(1) > button:nth-child(1)",function(){
                console.warn(browser.document.title);
                browser.assert.element("form");
            });
        })
    });
    */
});
