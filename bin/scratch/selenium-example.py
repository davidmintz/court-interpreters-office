
import unittest, time
from selenium import webdriver
from selenium.webdriver.common.keys import Keys

class InterpretersOffice(unittest.TestCase):

    def setUp(self):
        driver = webdriver.Firefox()
        #options.add_argument('--ignore-certificate-errors')
        #options.add_argument("--test-type")
        #options.binary_location = "/usr/bin/chromium-browser"
        #driver = webdriver.Chrome(options=options)
        self.driver = driver
        #self.driver = webdriver.Firefox()

    # def test_search_in_python_org(self):
    #     driver = self.driver
    #     driver.get("http://www.python.org")
    #     self.assertIn("Python", driver.title)
    #     elem = driver.find_element_by_name("q")
    #     elem.send_keys("pycon")
    #     elem.send_keys(Keys.RETURN)
    #     assert "No results found." not in driver.page_source

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
