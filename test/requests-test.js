/**  very rough draft of a test using mocha */

// var chai = require('chai'), expect = chai.expect, should = chai.should();
const Browser = require("zombie");
const moment = require("moment");
const assert = require("assert");
Browser.localhost("office.localhost");
const browser = new Browser();
const loader = require('./fixture/synchronous-load-request.js');

let request_id;

// note to self: this implicitly means "return new Promise(...)"
const load_data = () => new Promise(
    resolve => {
        //console.log(`we are running: ${loader.sql}`);
        loader.db.query(loader.sql,function(err){
            if (err) { throw err; }
        });
        loader.db.query("SELECT MAX(id) AS max FROM requests",function(err, results){
            request_id = results[0].max;
            resolve();
        })

    },
    reject => console.log("oops")
);


describe("updating a request that is scheduled",function(){
    before(function(){
        //console.log("step 1:  this is the before() function");
        return load_data();
    });
    it("schedule is automatically updated when user updates corresponding request",
    function(){
        browser.visit("/login")
        .then(function(){
            browser.assert.url({ pathname: "/login" });
            browser.fill("#identity","anthony_daniels@nysd.uscourts.gov");
            browser.fill("#password","boink");
            return browser.pressButton("log in");
        })
        .then(function(){
            browser.assert.success();
            return browser.visit(`/requests/view/${request_id}`);
        })
        .then(function(){
            // get event data for sanity check
            return new Promise(
                resolve => {
                    var sql = `SELECT e.date, e.modified, e.id,
                    ie.interpreter_id
                    FROM events e JOIN requests r ON r.event_id = e.id
                    JOIN interpreters_events ie ON ie.event_id = e.id
                    WHERE r.id = ${request_id}`;
                    loader.db.query(sql,function(err, result){
                        if (err) { console.log(err); throw err; }
                        resolve(result[0]);
                    });
                },
                reject  => { console.log("oops.");}
            );
        })
        .then(function(result){
            assert.ok(result);
            assert.ok(result.interpreter_id);
            var event_date = moment(result.date).format("YYYY-MM-DD");
            var request_date = loader.request_date.format("YYYY-MM-DD");
            assert.strictEqual(request_date, event_date);
        })
        .then(function(){
            browser.assert.success();
            //console.log("now we are at "+browser.location.href);
            browser.assert.url({ pathname: `/requests/view/${request_id}` });
            browser.assert.element("#date");
            var request_date = loader.request_date.format("ddd DD-MMM-YYYY");
            //console.log("expecting: "+request_date);
            browser.assert.text("#date",request_date);
            browser.assert.element("#request-details");
            browser.assert.elements("a.request-update",{atLeast : 1});
            return browser.fire("a.btn:nth-child(1)","click");
        })
        .then(function(){
            browser.assert.url({ pathname: `/requests/update/${request_id}` });
            browser.assert.element("#btn-save");
            browser.assert.element("#date");
            // change the date to one week later
            // clone the moment (implicitly)
            var new_date = moment(loader.request_date).add(7,"days");
            var date_str = new_date.format("MM/DD/YYYY");
            // this crashes jQuery ui datepicker:
            // browser.fill("#date",new_date.format("MM/DD/YYYY"));
            // ...so we use this workaround:
            browser.evaluate(`$("#request-form").append($("<input>").attr({name:"request[date]",value:"${date_str}"}));`);
        })
        .then(function(){
            return browser.pressButton("save");
        })
        .then(function(){
            browser.assert.url({ pathname: "/requests/list" });
            // did the event get updated automatically?
            return new Promise(
                resolve => {
                    var sql = `SELECT e.date, e.modified, e.id FROM events e JOIN requests r ON r.event_id = e.id WHERE r.id = ${request_id}`;
                    loader.db.query(sql,function(err, result){
                        if (err) { console.log(err); throw err; }
                        resolve(result[0]);
                    });
                },
                reject  => { console.log("oops.");}
            );
        })
        .then(function(result){
            assert.ok(result.id);
            var event_date = moment(result.date).format("YYYY-MM-DD");
            var request_date =  moment(loader.request_date).add(7,"days").format("YYYY-MM-DD");
            assert.strictEqual(request_date,event_date);
        }).then(function(){
            // did the interpreter get deleted automatically?
            var sql = `SELECT COUNT(*) AS interps_assigned FROM interpreters_events ie
                JOIN events e ON ie.event_id = e.id JOIN requests r ON e.id = r.event_id
                WHERE r.id = ${request_id}`;
            return new Promise(
                resolve => {
                    loader.db.query(sql,function(err, result){
                        if (err) { console.log(err); throw err; }
                        resolve(result[0].interps_assigned);
                    });
                },
                reject  => { console.log("shit happened.");}
            );
        }).then(function(result){
            assert.strictEqual(result,0);
        })
        .catch(function(error){
            console.log(error);
            throw error;
        }).finally(function(){
            //console.log("finally() is cleaning up dummy data");
            loader.unload();
            //loader.db.end();
            return browser.visit("/");
        });
    });

    after(function(){
        //loader.unload();
        //console.log("this cleanup is in after() and should appear last");

    });


});
