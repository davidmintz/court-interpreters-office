#!/usr/bin/env python3


import unittest, time
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.firefox.options import Options as FirefoxOptions

class InterpretersOffice(unittest.TestCase):

    def setUp(self):
        options = FirefoxOptions()
        # experience shows that if we want headless, we can use
        # the --headless option here, or else use e.g.,
        # MOZ_HEADLESS=1 pytest --testdox <path/to/tests>
        # options.add_argument("--headless")
        # options.add_argument('--ignore-certificate-errors')
        # options.add_argument("--test-type")
        driver = webdriver.Firefox(options=options)
        self.driver = driver

    def test_login_from_main_page(self):

        driver = self.driver
        driver.get("https://office.localhost")
        element = driver.find_element_by_css_selector("#login button")
        element.click()
        self.assertIn("user login",  driver.title)
        driver.find_element_by_id("identity").send_keys("david")
        driver.find_element_by_id("password").send_keys("boink")

        button = driver.find_element_by_css_selector("form button[type=submit]")
        button.click()
        self.assertNotIn("user login",  driver.title)


    def tearDown(self):
        self.driver.close()

if __name__ == "__main__":
    unittest.main()
