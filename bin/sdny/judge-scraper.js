/**
 * SDNY judge screen-scraper for node.js
 *
 * crawls over the USDJs and USMJs information on the nysd.uscourts.gov website
 * and compiles a basic directory -- name, courthouse, courtroom -- as JSON
 * data.
 *
 */

const axios = require("axios");
const jsdom = require("jsdom");
const https = require("https");
const { JSDOM } = jsdom;
const httpsAgent = new https.Agent({
    // unfortunate, but...
    rejectUnauthorized : false
    // ...because I couldn't solve the site's certificate errors
});
const client = axios.create({
    httpsAgent,
    baseURL : "https://nysd.uscourts.gov"
});

const get_judge_links = function(flavor){
    return new Promise(
        resolve => {
            client.get(`/judges/${flavor}-judges`,{httpsAgent})
            .then((response) => {
                var dom = new JSDOM(response.data);
                var links = dom.window.document.querySelectorAll(".judges-view-result a");
                resolve(links);
            })
            .catch((error)=>{console.warn("oops"); console.log(error)});
        }
    );
};

const parse_judge_info = function(url){
    return new Promise(
        resolve => {
            client.get(url,{httpsAgent}).then((response)=>{
                var dom = new JSDOM(response.data);
                var elements = dom.window.document
                    .querySelectorAll(".judge-detail-left, .courthouse-info");
                var courthouse, courtroom, match;
                elements.forEach(function(el){
                    var text = el.textContent.trim();
                    match = text.match(/courtroom:? *([a-z0-9-]+)\s+/i);
                    if (match) {
                        courtroom = match[1];
                        if (url.includes('goldberg')) {
                            console.error("courtroom match: "+courtroom);
                            console.error(text);
                        }
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

var data = {USMJ: {}, USDJ: {}};

(async function() {
    var usdj_links = await get_judge_links("district");
    for (var i = 0; i < usdj_links.length; i++) {
        var name = usdj_links[i].textContent.trim().replace(/^Hon. /,"");
        var url = usdj_links[i].href;
        // alas, there is no reliable way of parsing out first, middle and
        // last names any longer. it formerly was '<surnames>, <forenames and/or initials>'
        console.error(`fetching ${url} for: ${name}, USDJ`);
        data.USDJ[name] = await parse_judge_info(url);
    }
    var usmj_links = await get_judge_links("magistrate");
    for (var i = 0; i < usmj_links.length; i++) {
        var name = usmj_links[i].textContent.trim().replace(/^Hon. /,"");
        var url = usmj_links[i].href;
        console.error(`fetching ${url} for: ${name}, USMJ`);
        data.USMJ[name] = await parse_judge_info(url);
    }
    console.log(JSON.stringify(data));
})();
