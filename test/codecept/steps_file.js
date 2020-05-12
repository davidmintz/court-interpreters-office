// in this file you can append custom step methods to 'I' object
/* global actor */

module.exports = function() {
  return actor({

    // Define custom steps here, use 'this' to access default methods of I.
    // It is recommended to place a general 'login' function here.
    login : function(){
      this.amOnPage("/login");    
      this.fillField("#identity","david");
      this.fillField("#password","testing123");
      this.click({css: "button[type=submit]"});
      // console.log("this is the 'actor' login method")
    }

  });
}
