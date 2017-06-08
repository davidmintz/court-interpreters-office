/* 
not really part of the project, more of a learning exercise involving node.js
*/
var props = require("properties");
var options = {
  path: true,
  sections: true,
  comments: ";", //Some INI files also consider # as a comment, if so, add it, comments: [";", "#"]
  separators: "=",
  strict: true
};
props.parse('/home/david/.my.cnf',
	options,
	function(error,obj) {
		if (error) { console.error(error); } 
		console.log(obj.client);  
	}	
);
