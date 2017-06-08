/**
 * 
 * screen-scraper for CasperJs to get SDNY judge courtrooms
 * Usage: casperjs <scriptname>.js
 * 
 * this could all surely be done easier and fasting by piping curl output to awk or similar. but
 * we want to learn some Casper.
 */

var casper = require('casper').create({});

// var fileUrl = "file:///opt/www/court-interpreters-office/public/judges.html";

var baseUrl = "http://nysd.uscourts.gov";

casper.start(baseUrl+ '/judges/District', function() {
  
   this.waitForSelector('table.judge_info'); 
   // get an object in the form { judge_name : url, ... }
   links = this.evaluate(function(){       
       var links = jQuery("table.judge_info tbody tr td a");
       $return = {};
       links.each(function(i,e){
           $return[e.textContent.trim()] = e.getAttribute('href');
       });
       return $return;       
   });
});

casper.then(function(){   
    for(var judge in links) {
       var url = baseUrl + "/" + links[judge];
       // and now, use a IIFE and tear your hair out no more! 
       // http://stackoverflow.com/questions/24360993/casperjs-iterating-through-urls
       // https://groups.google.com/forum/#!topic/casperjs/n_zXlxiPMtk
       (function(url,name){
           casper.thenOpen(url,function(){
               this.echo(name+ ":  "+this.getCurrentUrl());
               data = this.evaluate(function() {
                    var text = '';
                    var elements = jQuery("table#text.main tbody tr.whiteback td table tbody tr td p:lt(2)");
                    elements.each(function (i, e) {
                        text += e.innerHTML.replace(/\s*<br *\/?>\s*/g, "\n").trim();
                        text += "\n";
                    });
                    return text;
                });
               this.echo(data);
           });})
       (url,judge);       
    }  
});
casper.run();
