#!/usr/bin/env python3

# possibly useful in tests, for getting html payload from
# .eml files produced by Zend\Mail

from email import parser;
import argparse;

args = argparse.ArgumentParser()
args.add_argument("filename", help="path to .eml file")
filename = args.parse_args().filename
data = open(filename)
msg = parser.Parser().parse(data)
html = msg.get_payload()[1]
thing = str(html.get_payload(decode=True))
print(thing.replace('\\n','\n'))
