/**  very rough draft of a test using mocha */

// var chai = require('chai'), expect = chai.expect, should = chai.should();
// const Browser = require("zombie");
// const assert = require("assert");

loader = require('./synchronous-load-request.js');
const myFunction = () => new Promise(
    resolve => {
        console.log(`we are running: ${loader.sql}`);
        loader.db.query(loader.sql,function(err){
            if (err) { throw err; }
        });
        resolve();
    },
    reject => {console.log("oops")}
);

describe("here shit goes",function(){
    before(function(){
        console.log("step 1:  this is the before() function");
        return myFunction();
    });
    it("should work",function(done){
        console.log("step 2: this is the actual test");
        done();
    });

    after(function(){
        loader.unload();
        console.log("this cleanup is in after() and should appear last");
    });
});
