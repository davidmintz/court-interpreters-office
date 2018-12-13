#!/usr/bin/env node

/**  crude attempt to seed our database with some dummy data */

const mysql = require('mysql');
const fs = require('fs');
const ini = require('ini');
const moment = require("moment");
var params = ini.parse(fs.readFileSync(`${process.env.HOME}/.my.cnf`, 'utf-8')).client;

let db = mysql.createConnection({
    user : params.user,
    password : params.password,
    database : "office",
    multipleStatements: true
});
var date = moment().startOf("week" ).add(37,"days").format("YYYY-MM-DD");
var req_created = moment().startOf("week" ).subtract(4,"days").hour(15).minutes(23);
var event_created = req_created.add(40,"minutes").format("YYYY-MM-DD HH:mm:ss");
var req_created_str = req_created.format("YYYY-MM-DD HH:mm:ss");
var request_insert =
`INSERT INTO requests VALUES (null,'${date}','11:00:00',2593,NULL,16,62,'2018-CR-0611',62,1047,'${req_created_str}','${req_created_str}',31,'dummy request for automated testing',null,0,0,'');
INSERT INTO defendants_requests VALUES (23805,LAST_INSERT_ID())`
var event_insert = `INSERT INTO events VALUES (NULL,62,2593,1047,NULL,'${date}','11:00:00',NULL,'2018-CR-0611','dummy event for automated test','','${event_created}',
    '${event_created}',41,524,NULL,NULL,NULL,31,'${req_created_str.substring(0,10)}','${req_created_str.substring(11)}');
    INSERT INTO defendants_events VALUES (LAST_INSERT_ID(),23805)`;
var request_update = `SET @id = (SELECT max(id) FROM requests); UPDATE requests SET event_id = LAST_INSERT_ID() WHERE id = @id`

const sql = `${request_insert}; ${event_insert}; ${request_update};`;

const unload = function(){
    var sql = 'DELETE FROM requests ORDER BY id DESC LIMIT 1; DELETE FROM events ORDER BY id DESC LIMIT 1';
    db.query(sql);
    db.end();

};

module.exports  = { db, sql, unload };



/* this is bullshit ....
var __load = function()
{

    var date = moment().startOf("week" ).add(23,"days").format("YYYY-MM-DD");
    var req_created = moment().startOf("week" ).subtract(4,"days").hour(15).minutes(23);
    var event_created = req_created.add(40,"minutes").format("YYYY-MM-DD HH:mm:ss");
    req_created_str = req_created.format("YYYY-MM-DD HH:mm:ss");
    var request_insert =
    `INSERT INTO requests VALUES (null,'${date}','11:00:00',2593,NULL,16,62,'2018-CR-0611',62,1047,'${req_created_str}','2018-10-10 14:11:18',31,'',null,0,0,'')`;
    var event_insert = `INSERT INTO events VALUES (NULL,62,2593,1047,NULL,'${date}','11:00:00',NULL,'2018-CR-0611','dummy event for automated test','','${event_created}',
        '${event_created}',41,524,NULL,NULL,NULL,31,'${req_created_str.substring(0,10)}','${req_created_str.substring(11)}')`;
    db.query(request_insert,function(error, result,fields){
        if (error) {
            throw error;
        }
        var request_id = result.insertId;
        console.log(`request was inserted with id ${request_id}`);
        var deft_insert = `INSERT INTO defendants_requests VALUES (23805,${request_id});`
        db.query(deft_insert,function(error){ if (error) {throw error;} console.log("deft-request was inserted") });
        db.query(event_insert,function(error,result){
            if (error) { throw error; }
            var event_id = result.insertId;
            console.log(`event was inserted with id ${event_id}`);
            var deft_insert = `INSERT INTO defendants_events VALUES (${event_id},23805);`
            db.query(deft_insert,function(error){ if (error) {throw error;} console.log("deft-event was inserted"); });
            db.end();
        });
    });
};
*/
