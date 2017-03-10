/**
 * 
 * screen-scraper for CasperJs to get judge courtrooms
 * invoke as casperjs <scriptname>.js
 * 
 */

var links;
var casper = require('casper').create({
    verbose:true
    //clientScripts: ["../public/js/lib/jquery.min.js"]
});
//require('fs');
//var fs = require('fs');
var fileUrl = "file:///opt/www/court-interpreters-office/public/judges.html";
var url = "http://nysd.uscourts.gov/judges/District";
var links;

casper.start(url, function() {
  
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
   this.echo("leaving 1st function. return is "+typeof links);
});

casper.then(function(){
    
    // this.echo("this is your second function, links is a "+typeof links);
    // var json = JSON.stringify(links,null,5);
    var count = 0;
    for (item in links) {
        count++;
        var url = "http://nysd.uscourts.gov/"+links[item];        
         if (count > 1) {
            this.echo("debug: stopping iteration");
            break;
        }
        this.echo(item +": url: http://nysd.uscourts.gov/"+links[item]);
        casper.open(url).then(function(){
            // parse out the text from the first two td > p elements in the table
            data = this.evaluate(function(){
                var text = '';
                var elements = jQuery("table#text.main tbody tr.whiteback td table tbody tr td p:lt(2)");
                elements.each(function(i,e){
                    text += e.innerHTML.replace(/<br *\/?>/g,"\n").trim();
                    text += "\n";
                    }
                )
                return text;
            });            
            this.echo(data);
        });
    }
    
    
});

casper.run();