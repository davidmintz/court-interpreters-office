#!/usr/bin/env python3

'''
A first stab at a screen-scraper for mining ECF data.

Among many to do items: use a configuration file for login credentials
'''
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import argparse

parser = argparse.ArgumentParser(description='scrape ECF for a docket report')
parser.add_argument("--username", help="your ECF username", required=True)
parser.add_argument("--password",help="your ECF password",required=True)
parser.add_argument("--docket",help="docket number to search for",required=True)

args = parser.parse_args();
login_url = "https://jenie.ao.dcn/nysd-ecf/cgi-bin/login.pl"

driver = webdriver.Firefox()
driver.get(login_url)
driver.find_element_by_css_selector("input[name='usernameEntered']").send_keys(args.username)
driver.find_element_by_css_selector("#password").send_keys(args.password)
driver.find_element_by_css_selector("#SUBMIT2").click()
driver.get("https://jenie.ao.dcn/nysd-ecf/cgi-bin/iquery.pl")
driver.find_element_by_id("case_number_text_area_0").send_keys(args.docket);
try:
    element = WebDriverWait(driver, 3).until(
        EC.visibility_of_element_located((By.ID,"case_number_find_button_0"))
    )
    element.click()

    '''
    <input type="button" id="case_number_hide_button_0" style="display: inline; width: 8em;" value="Hide Case List">
    '''
    # this means we got results
    element = WebDriverWait(driver,6).until(
        EC.visibility_of_element_located((By.ID,"case_number_hide_button_0"))
    )
    # which may look like...
    '''
    <div id="case_number_pick_area_0" style="white-space: nowrap; display: inline; background-color: rgb(255, 255, 204); border: 2px solid red"><br>Select a case:<div style="background-color: rgb(255, 255, 204);" id="case_line_0_507166">
        <input autocomplete="off" type="checkbox" style="cursor: pointer;" name="checkbox_0" id="checkbox_0_507166" value="1:18-cr-906"><a style="cursor: pointer;">1:18-cr-00906-WHP USA v. Frasco et al</a> <img src="/graphics/plus.gif" alt="+" style="display: none;" id="expand_button_0_507166" width="9" height="9">
        <img src="/graphics/minus.gif" alt="-" style="display: inline;" id="collapse_button_0_507166" width="9" height="9"></div><div style="background-color: rgb(255, 255, 204); display: block;" id="case_line_0_507167">
        <input autocomplete="off" type="checkbox" style="cursor: pointer; margin-left: 2em;" name="checkbox_0" id="checkbox_0_507167" value="1:18-cr-906-1|507167|1|"><a style="cursor: pointer;">1:18-cr-00906-WHP-1 Agustin Arreola Frasco</a></div><div style="background-color: rgb(255, 255, 204); display: block;" id="case_line_0_507168">
        <input autocomplete="off" type="checkbox" style="cursor: pointer; margin-left: 2em;" name="checkbox_0" id="checkbox_0_507168" value="1:18-cr-906-2|507168|2|"><a style="cursor: pointer;">1:18-cr-00906-WHP-2 Eduard Veisyan</a></div>
    </div>
    '''
 #  or...
    '''
    <span id="case_number_message_area_0" style="font-size: small;"> Cannot find case 18-cr-23787343</span>
    '''
    print(driver.page_source)

finally:
    driver.quit()


# shit = driver.page_source
# print(shit)
# time.sleep(3)
# selenium.webdriver.support.expected_conditions.visibility_of_element_located(locator)
# driver.quit()
