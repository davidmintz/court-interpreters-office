/* global Feature, Before, Scenario, locate */

Feature('MOTD via schedule');

// Before(login => {
//    // login('admin'); // login using user session
//  });

// Scenario('view schedule', (I) => {
//     // I.login();
//     I.amOnPage("admin/schedule/2020/05/13");
//     I.see("Wed 13 May 2020");
// });

Scenario('display MOTD', (I) => {

    I.login();
    I.amOnPage("admin/schedule/2020/05/13");
    I.see("Wed 13 May 2020");
    var link = locate("a.nav-link").withText("notes");
    I.click(link);
    I.waitForElement("#btn-motd");
    I.click("#btn-motd");
    I.waitForElement("#motd");
    I.seeElement("#motd");
    I.see("MOTD: Wed 13-May-2020",{css:"#motd"});
    I.click("#motd a.btn");
    I.see("MOTD for Wednesday 13-May-2020",{css: "#motd-content h4"});
    I.seeElement("textarea[name=content]");
    I.fillField("textarea[name=content]","This is now the MOTD for Wed 13-May-2020");



});