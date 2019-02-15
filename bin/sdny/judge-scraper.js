/**
 * effort to write a screen scraper with node for SDNY judges,
 * still haven't entirely mastered this asynchronous thing
 */

const axios = require("axios");
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
var data = {USMJ: {}, USDJ: {}};

axios.get("http://nysd.uscourts.gov/judges/District")
.then(function(response){
     var dom = new JSDOM(response.data);
     var links = dom.window.document.querySelectorAll("table.judge_info tr td a");
     return links;
 })
.then(function(links){
     var num_links = links.length;
     for (var i = 0; i < num_links; i++) {
         var el = links[i];
        (function(el,i){
            //console.log(`fetching ${el.href} for ${el.textContent}...${i} of ${links.length}`);
            return axios.get(el.href)
            .then(function(response){
                var name = el.textContent.trim();
                var doc = new JSDOM(response.data).window.document;
                var elements = doc.querySelectorAll(".mainblock table tr.whiteback td table tr td p");
                //console.log(`name is: ${name}`);
                //data.USDJ[name] = {};
                var courthouse, courtroom, match;
                elements.forEach(function(el){
                    var text = el.textContent.trim();
                    var match = text.match(/courtroom:? *(\S+)\s+/i);
                    if (match) {
                        courtroom = match[1];
                    }
                    match = text.match(/(500 Pearl|40 Foley|White Plains)/);
                    if (match) {
                        courthouse = match[1];
                    }
                });
                data.USDJ[name] = {courthouse, courtroom };
                if (Object.keys(data.USDJ).length === links.length) {
                    //return data;
                    console.log(JSON.stringify(data));
                }
            })
            // .then((data)=>{
            //     if (data){
            //         console.log(JSON.stringify(data));
            //     }
            // })
            .catch(function(err){console.log(err)});;
        })(el,i);
     }
 })
.catch(function(err){console.log(err)});

// and do it again for Magistrates?...
/*
axios.get("http://nysd.uscourts.gov/judges/Magistrate")
.then(function(response){
     var dom = new JSDOM(response.data);
     var links = dom.window.document.querySelectorAll("table.judge_info tr td a");
     data.USMJ._total = links.length;
     return links;
 })
.then(function(links){
     for (i = 0; i < links.length; i++) {
         var el = links[i];
        (function(el,i){
            return axios.get(el.href)
            //console.log(`fetching ${el.href} for ${el.textContent}...`);
            .then(function(response){
                var name = el.textContent.trim();
                var doc = new JSDOM(response.data).window.document;
                var elements = doc.querySelectorAll(".mainblock table tr.whiteback td table tr td p");
                //console.log(`name is: ${name}`);
                data.USMJ[name] = {};
                elements.forEach(function(el){
                    var text = el.textContent.trim();
                    //var courthouse,courtroom;
                    if (m = text.match(/courtroom:? *(\S+)\s+/i)) {
                        data.USMJ[name].courtroom = m[1];
                        //console.log(`courtroom is ${data[name].courtroom}`);
                    }
                    if (m = text.match(/500 Pearl|40 Foley|White Plains/)) {
                        data.USMJ[name].courthouse = m[1];
                    }
                });
                if (i == links.length - 1) {
                    return data;
                }
            })
            .then((data)=>{
                //if (data){console.log(data); console.log("done? FOR REAL??")}
            });
        })(el,i);
     }
 })
.catch(function(err){console.log(err)});
*/
