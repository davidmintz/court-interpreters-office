/** laerning exercise for zombie and mocha */
// https://mochajs.org/#getting-started
// http://zombie.js.org

// npm install mocha and zombie
// then:  node_modules/mocha/bin/mocha <path/to/>zombie.js
//


var chai = require('chai'), expect = chai.expect, should = chai.should();
const Browser = require("zombie");
const assert = require("assert");


Browser.localhost("office.localhost", 80);

const browser = new Browser();
describe("example test",function(){

    //helper.login(browser).then(console.log("and?"));

    before(function(done) {
        browser.visit("/", done);
    });
    it("should be successful", function(done) {
        browser.assert.success();
        done();
    });


    it("should see welcome page", function() {
         browser.assert.text("title", "Court Interpreters Office");
    });
    //
    it("should have a login button", function() {
        // the example on their site had these calls chained but
        // that didn't work for us
        browser.assert.element("a:nth-child(1) > button:nth-child(1)");
        browser.assert.text("#login","log in");
        browser.assert.evaluate("$(\"#login\").length", 1);
        browser.assert.evaluate("$(\"#login\").attr(\"href\")", "/login");
    });
});
describe("log in admin user",function(){
    before(function(done) {
        return browser.visit("/login",done);
    });
    it("should load login page",function(){
        browser.assert.success();
    });
    it("should let me submit log in",function(done){
        browser.fill("#identity","david");
        browser.fill("#password","boink");
        return browser.pressButton("log in",done);
    });
    it("should authenticate me",function(done){
        browser.assert.success();
        assert.ok(browser.document.title.indexOf("admin") !== -1);
        // to do: assert "david" is displayed
        done();
    });
});
describe("do more shit",function(){
    before(function(done) {
        return browser.visit("/admin/schedule",done);
    });
    it("should load schedule page",function(){
        browser.assert.success();
    });
    // apparently this will seem to hang for about as long as the refresh interval
});
