/** 
 *  example demonstrating how to log in and then navigate to somewhere
 *  requiring authentication using CasperJs
 * 
 */


// these callbacks do not seem to be necessary just to 
// persist the authentication between requests but we were
// having trouble with that, or so we thought

var resourceReceived = function(casper,response) {
    //console.log(response.url + " is our url");
    if (response.status) {
       // console.log(response.status + " is your status");
    }
    if (response.contentType.indexOf('text/html') === 0) {
       this.log("[us] got response from " + response.url,'info');
    }
    // it looks like phantom.cookies === casper.page.cookies
    fs.write(cookieJar, JSON.stringify(phantom.cookies), "w");
    fs.write('./elsewhere.json',JSON.stringify(casper.page.cookies), "w");
};
var resourceRequested = function(casper, resource) {

    if (fs.isFile(cookieJar)) {
        var cookies = JSON.parse(fs.read(cookieJar));// array of objects
        if (cookies.length) {
            for (var i = 0; i < cookies.length; i++) {
                if (! phantom.addCookie(cookies[i])) {
                   console.log("[us] shit? failed to add cookie "+cookies[i].name);
                } 
            }
        }
    }   
};

var cookieJar = 'cookies.json';
var utils = require('utils');
var fs = require('fs');
var baseUrl = 'http://localhost:5000';

var casper = require('casper').create({
    verbose:true,
    logLevel:'info'
    //, // see comment above
    //onResourceReceived: resourceReceived,
    //onResourceRequested: resourceRequested
});

/*
 * from phantom js doc:
 The response metadata object contains these properties:

    id : the number of the requested resource
    url : the URL of the requested resource
    time : Date object containing the date of the response
    headers : list of http headers
    bodySize : size of the received content decompressed (entire content or chunk content)
    contentType : the content type if specified
    redirectURL : if there is a redirection, the redirected URL
    stage : “start”, “end” (FIXME: other value for intermediate chunk?)
    status : http status code. ex: 200
    statusText : http status text. ex: OK

/*/



casper.start(baseUrl + '/',function(status){
    this.log("loaded /");
    
});
// assumes we have a dummy user with username 'somebody' and password 'boink'
casper.thenOpen(baseUrl + '/login',function(){
    this.log("attempting login",'warning');
    this.fillSelectors('form', {
        '#identity':    'somebody',
        '#password':    'boink'
    }, true);   
});

casper.then(function(){
    this.log("here we FUCKING are!!",'warning');
});

casper.thenOpen(baseUrl + '/admin/languages',
    function(){
        this.log("we seem to have loaded a page now, do we not?",'info');
        this.log(this.getTitle(),'info');
        this.echo(this.getHTML('table > tbody'));
    }
);

//console.log("here we are");
//console.log("phantom.page is a:" + typeof phantom.page);
//console.log("phantom.page.cookies is a:" + typeof phantom.page.cookies);
//console.log(utils.dump(phantom.page.cookies));
casper.run();


