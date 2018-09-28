#!/usr/bin/env python

import cgi, cgitb
cgitb.enable()
params  = cgi.FieldStorage()

#cgi.test()
print("Content-type: text/html\n\n")
print("Hello world<br>" )

print(params.getvalue("foo"))


"""
	<Directory /opt/www/interpreters/public/admin/other/reports >
		Options +ExecCGI
 		AddHandler cgi-script .py
	</Directory>
"""
