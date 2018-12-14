/**  very rough draft of a test using mocha */

// var chai = require('chai'), expect = chai.expect, should = chai.should();
const Browser = require("zombie");
const moment = require("moment");
// const assert = require("assert");
Browser.localhost("office.localhost");
const browser = new Browser();

const loader = require('./synchronous-load-request.js');
const myFunction = () => new Promise(
    resolve => {
        //console.log(`we are running: ${loader.sql}`);
        loader.db.query(loader.sql,function(err){
            if (err) { throw err; }
        });

        loader.db.query("SELECT MAX(id) AS max FROM requests",function(err, results){
            resolve(results[0].max);
        })

    },
    reject => {console.log("oops")}
);
let request_id;

describe("here shit goes",function(){
    before(function(){
        console.log("step 1:  this is the before() function");
        var promise = myFunction();
        promise.then((value)=>{
            // seems to make request_id accessible to it(...)
            request_id = value;
        });

        return promise;
    });
    it("should work",function(){
        console.log("step 2: this is an actual test, our request id is "+request_id);
        browser.visit("/login")
        .then(function(){
                console.log("now we are at "+browser.window.document.location.href);
            browser.fill("#identity","anthony_daniels@nysd.uscourts.gov");
            browser.fill("#password","boink");
            return browser.pressButton("log in");
        })
        .then(function(){
            //console.log("here comes an assertion");
            browser.assert.success();
            return browser.visit(`/requests/view/${request_id}`);
        })
        .then(function(){
            browser.assert.success();
                console.log("now we are at "+browser.location.href);
            //console.log(browser.source);
            browser.assert.element("#date");
            var request_date = loader.request_date.format("ddd DD-MMM-YYYY");
            //console.log("expecting: "+request_date);
            browser.assert.text("#date",request_date);
            browser.assert.element("#request-details");
            browser.assert.elements("a.request-update",{atLeast : 1});
            return browser.fire("a.btn:nth-child(1)","click");
        })
        .then(function(){
            // test something else
            console.log("now we are at "+browser.location.href);
            browser.assert.element("#btn-save");
            browser.assert.element("#date");
            // change the date to one week later
            // clone the moment (implicitly)
            var new_date = moment(loader.request_date).add(7,"days");
            console.log("Setting date to: "+new_date.format("MM/DD/YYYY"));
            browser.fill("#date",new_date.format("MM/DD/YYYY"));
            console.log(`browser.pressButton is a fucking ${typeof browser.pressButton}`);
            // //return browser.evaluate('$("#btn-save").trigger("click")');
            // //var form = browser.document.getElementById("request-form");
            return browser.pressButton("save");

            //return browser.document.getElementById("request-form").submit();
            //return browser.fire("#btn-save","click");
        })
        .catch(function(error){
            console.log(error);
            throw error;
        }).finally(function(){
            loader.unload();
            loader.db.end();
        });




    });
    // it("should work some more",function(){
    //     // we are still back at /login because of the asynchronicity
    //     console.log(browser.window.document.location.href);
    // });
    after(function(){
        //loader.unload();
        console.log("this cleanup is in after() and should appear last");

    });


});
