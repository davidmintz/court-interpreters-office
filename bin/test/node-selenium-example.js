/* npm install selenium-webdriver@3.6.0 */

var webdriver = require('selenium-webdriver'),
    By = webdriver.By,
    until = webdriver.until;

var driver = new webdriver.Builder()
    .withCapabilities({'browserName': 'firefox', acceptSslCerts: true, acceptInsecureCerts: true})
    .forBrowser('firefox')
    .build();

//driver.get("https://office.localhost");
driver.get("https://davidmintz.org");
