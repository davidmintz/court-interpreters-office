#!/usr/bin/env python3

from selenium import webdriver
from selenium.webdriver.common.keys import Keys
import time
import unittest

class PythonAcceptanceTest(unittest.TestCase):

    def setUp(self):
        options = webdriver.ChromeOptions()
        options.add_argument('--ignore-certificate-errors')
        #options.add_argument("--test-type")
        options.binary_location = "/usr/bin/chromium-browser"
        webdriver.Chrome(options=options)
        self.driver =  webdriver.Chrome(options=options)

    def tearDown(self):
        self.driver.close()



    def test_main_page(self):
        #print("testing sanity")
        self.driver.get('https://office.localhost')
        assert "Court Interpreters Office" in self.driver.title

    def test_login(self):
        self.driver.get('https://office.localhost/login')
        self.assertIn('login',self.driver.title)
        identity_field = self.driver.find_element_by_name("identity");
        identity_field.send_keys('david')
        password_field = self.driver.find_element_by_name("password");
        password_field.send_keys("boink")
        # /html/body/div[1]/div[1]/div/form/button
        # html body div.container div.row div.offset-lg-3 form#form-login.form-inline button.btn.btn-info.ml-1
        button = self.driver.find_element_by_css_selector("#form-login button")
        button.click()
        self.assertIn('/admin',self.driver.current_url)

if __name__ == "__main__":
    unittest.main()

'''
options = webdriver.ChromeOptions()
options.add_argument('--ignore-certificate-errors')
#options.add_argument("--test-type")
options.binary_location = "/usr/bin/chromium-browser"
driver = webdriver.Chrome(chrome_options=options)
driver.get('https://office.localhost')

assert "Court Interpreters Office" in driver.title

driver.close()

'''
