const path = "/opt/www/sdny/court-interpreters-office/files/public/judges.json";
const scraper = require("./scrape.js");
const cron = require("node-cron");
const express = require("express");
const fs = require("fs");
const app = express();

cron.schedule("32 10,14,17 * * 1-5",()=>{
    // console.log("running at "+new Date().toString())
    scraper.run()
    .then((data)=> {
        console.log(data);
        fs.writeFile(path,data,'utf8',
        (err)=> { if (err) { throw err; }})
     });
});
// console.log("starting server...");
app.listen(3128);
