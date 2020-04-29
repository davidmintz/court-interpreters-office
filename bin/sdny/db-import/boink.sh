#!/bin/bash

# set everyone's password to "boink"
# not intended for production use :-)

hash=$(php -r 'echo password_hash("boink",PASSWORD_DEFAULT);')
echo "UPDATE users SET password='$hash'" | mysql office
