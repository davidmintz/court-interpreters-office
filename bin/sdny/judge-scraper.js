
const axios = require("axios");
const jsdom = require("jsdom");
const { JSDOM } = jsdom;

var data = {};

axios.get("http://nysd.uscourts.gov/judges/District")
.then(function(response){
         var dom = new JSDOM(response.data);
         var links = dom.window.document.querySelectorAll("table.judge_info tr td a");
         return links;

 })
 .then(function(links){
     for (i = 0; i < links.length; i++) {
         var el = links[i];
        (function(el,i){
            axios.get(el.href)
            //console.log(`fetching ${el.href} for ${el.textContent}...`);
            .then(function(response){
                var name = el.textContent.trim();
                var doc = new JSDOM(response.data).window.document;
                var elements = doc.querySelectorAll(".mainblock table tr.whiteback td table tr td p");
                //console.log(`name is: ${name}`);
                data[name] = {};
                elements.forEach(function(el){
                    var text = el.textContent.trim();
                    var courthouse,courtroom;
                    if (m = text.match(/courtroom:? *(\S+)\s+/i)) {
                        data[name].courtroom = m[1];
                        //console.log(`courtroom is ${data[name].courtroom}`);
                    }
                    if (text.match(/500 Pearl/)) {
                        courthouse = "500 Pearl";
                    } else if (text.match(/White Plains/)) {
                        //300 Quarropas
                        courthouse = "White Plains";
                    }  else if (text.match(/40 Foley/)) {
                        courthouse = "40 Foley"
                    }
                    if (courthouse) {
                        data[name].courthouse =  courthouse;
                        //console.log(`courthouse is ${data[name].courthouse}`);
                    }
                });
                if (i == links.length - 1) {
                    return data;
                }
            })
            .then((data)=>{
                if (data){console.log(data); console.log("done?")}
            });
        })(el,i);
     }
 })

 .catch(function(err){console.log(err)});


//const dom = new JSDOM(`<!DOCTYPE html><p>Hello world</p>`);
// var jsdom = require("jsdom");
// const axios = require("axios");
// const { JSDOM } = jsdom;
// const { window } = new JSDOM();
// const { document } = (new JSDOM('')).window;
// global.document = document;
//
// var $ = jQuery = require('jquery')(window);
// axios.get("http://nysd.uscourts.gov/judges/District")
// .then(function(response){
//     //console.log(response.data);
//     var doc = $(response.data);
//     var links = doc.find("a");
//     console.log(links);
// });
