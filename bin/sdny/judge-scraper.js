
const axios = require("axios");
const jsdom = require("jsdom");
const { JSDOM } = jsdom;

var data = {USMJ: {}, USDJ: {}};

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
                resolve({courthouse,courtroom});
            });
        }
    );
};

(async function() {

    var usdj_links = await get_judge_links("District");
    for (var i = 0; i < usdj_links.length; i++) {
        var name = usdj_links[i].textContent.trim();
        var url = usdj_links[i].href;
        //console.log(`fetching ${url} for: ${name}, USDJ`);
        info = await parse_judge_info(url);
        if (info.courthouse && info.courtroom) {
            data.USDJ[name] = info;
        }
        //data.USDJ[name] = await parse_judge_info(url);
    }
    var usmj_links = await get_judge_links("Magistrate");
    for (var i = 0; i < usmj_links.length; i++) {
        var name = usmj_links[i].textContent.trim();
        var url = usmj_links[i].href;
        //console.log(`fetching ${url} for: ${name}, USMJ`);
        //info = await parse_judge_info(url);
        if (info.courthouse && info.courtroom) {
            data.USMJ[name] = info;
        }
        //data.USMJ[name] = await parse_judge_info(url);
    }
    console.log(JSON.stringify(data));
})();
