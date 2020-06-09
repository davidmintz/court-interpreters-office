#!/bin/bash

# Download the current SDNY judges' directory (PDF) from the internal website
# (even though they change the filename with each new update)
# This will only work inside the SDNY network, and even there 
# it is subject to breakage if certain things change.

# where to put the PDF
dir="/opt/www/court-interpreters-office/public/files/judges";

# our bin directory
bindir='/opt/www/court-interpreters-office/bin/sdny';

# base url to look for file
base_url="http://intranet.nysd.circ2.dcn/"

# get into our bin directory if need be
if [ $PWD != $bindir ] ; then cd $bindir; fi;

# parse out the url to pdf file
path=$(curl -s $base_url |  perl  -n -e '/href="(.+directories\/Chambers\/[^"]+pdf)"/ && print $1,qq(\n)' |head -n 1)
url="${base_url}/${path}"

# our local filename
basename=$(basename "$url")
# minus the spaces! oh, how I hate spaces in file names.
filename=$(echo $basename | sed -e 's/ /_/g')

# download the file
wget -q "$url" -O $filename
if [ $? -ne 0 ]; then 
    echo "download failed with error code $?"
    exit 1 
fi;

# do we already have this file?
if [ ! -e "${dir}/${filename}" ]; then
    # does not exist, so move it
    mv "${filename}" "${dir}/"; 
    # ...and symlink it to a sensible name
    ln -s --force "${dir}/${filename}" ${dir}/directory.pdf
else
    # if we do have this file, has it changed since last time?
    cmp -s "${filename}" "${dir}/${filename}";
    if [ $? -ne 0 ]; then 
        # change detected, move $filename;
        mv "${filename}" "${dir}/${filename}"; 
    else 
        # nothing to do; delete downloaded file
        rm "${filename}";        
    fi;
fi;
exit 0;
