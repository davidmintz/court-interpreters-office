
const axios = require("axios");
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
var data = {USMJ: {}, USDJ: {}};
// var resolveAfter2Seconds = function() {
//   console.log("starting slow promise");
//   return new Promise(resolve => {
//     setTimeout(function() {
//       resolve(20);
//       console.log("slow promise is done");
//     }, 2000);
//   });
// };
//
// var resolveAfter1Second = function() {
//   console.log("starting fast promise");
//   return new Promise(resolve => {
//     setTimeout(function() {
//       resolve(10);
//       console.log("fast promise is done");
//     }, 1000);
//   });
// };

const get_judge_links = function(flavor){
    return new Promise(
        resolve => {
            axios.get(`http://nysd.uscourts.gov/judges/${flavor}`)
            .then((response) => {
                var dom = new JSDOM(response.data);
                var links = dom.window.document.querySelectorAll("table.judge_info tr td a");
                resolve(links);
            })
            .catch((error)=>{console.warn("oops"); console.log(error)});
        },
        reject => {console.warn("oops")}
    );
};
const parse_judge_info = function(url){

    return new Promise(
        resolve => {
            axios.get(url).then((response)=>{
                var dom = new JSDOM(response.data);
                var elements = dom.window.document
                    .querySelectorAll(".mainblock table tr.whiteback td table tr td p");
                
            })
        }
    );
};
const parseJudgeData = function(elements){
    var courthouse, courtroom, match;
    elements.forEach(function(el){
        var text = el.textContent.trim();
        match = text.match(/courtroom:? *(\S+)\s+/i);
        if (match) {
            courtroom = match[1];
        }
        match = text.match(/(500 Pearl|40 Foley|White Plains)/);
        if (match) {
            courthouse = match[1];
        }
    });
    return { courthouse, courtroom };
};

async function scrape() {

    var usdj_links = await get_judge_links("District");
    var usmj_links = await get_judge_links("Magistrate");

    console.log([usdj_links,usmj_links]);


};


scrape();
