#!/usr/bin/env python3

import unittest, time, configparser, os, pymysql
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC, select
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait

'''
a learning exercise
further reading:
https://blog.testproject.io/2019/07/16/open-source-test-automation-python-pytest-selenium-webdriver/
'''
class InterpreterAdminTests(unittest.TestCase):

    @classmethod
    def setUpClass(cls):

        cls.purge()
        # options = FirefoxOptions()
        # experience shows that if we want headless, we can use
        # the --headless option here, or else use e.g.,
        # MOZ_HEADLESS=1 pytest --testdox <path/to/tests>
        # options.add_argument("--headless")
        # options.add_argument('--ignore-certificate-errors')
        # options.add_argument("--test-type")
        driver = webdriver.Firefox()

        driver.get("https://office.localhost/login")
        # element = driver.find_element_by_css_selector("#login button")
        # element.click()

        driver.find_element_by_id("identity").send_keys("david")
        driver.find_element_by_id("password").send_keys("boink")

        button = driver.find_element_by_css_selector("form button[type=submit]")
        button.click()
        # self.assertNotIn("user login",  driver.title)
        cls.driver = driver

    @classmethod
    def purge(cls):
        config_file = os.environ["HOME"]+"/.my.cnf"
        config = configparser.ConfigParser()
        config.read(config_file)
        params = config["client"]
        db = pymysql.connect(user=params["user"],password=params["password"],
                             db="office")
        cursor = db.cursor();
        query = "DELETE FROM people WHERE email = 'gacker_boink@nysd.uscourts.gov'"
        cursor.execute(query)
        db.commit()
        cls.db = db



    def setUp(self):
        # options = FirefoxOptions()
        # experience shows that if we want headless, we can use
        # the --headless option here, or else use e.g.,
        # MOZ_HEADLESS=1 pytest --testdox <path/to/tests>
        # options.add_argument("--headless")
        # options.add_argument('--ignore-certificate-errors')
        # options.add_argument("--test-type")
        # driver = webdriver.Firefox(options=options)
        # self.driver = driver
        pass

    def test_interpreter_add_form_validation(self):

        driver = self.driver
        driver.get("https://office.localhost/admin/interpreters/add")
        driver.find_element_by_id("lastname").send_keys("Gacker")
        driver.find_element_by_id("firstname").send_keys("Boink")
        driver.find_element_by_id("email").send_keys("gacker_boink@nysd.uscourts.gov");
        driver.find_element_by_id("languages-tab").click()
        wait = WebDriverWait(driver, 3)
        wait.until(EC.visibility_of_element_located((By.ID, 'language-select')))
        driver.find_element_by_id("comments").send_keys("these are some comments, carefully considered.")
        driver.find_element_by_css_selector("#language-select option[value='3']").click()
        driver.find_element_by_id("btn-add-language").click()

        element_name = "interpreter[interpreterLanguages][0][languageCredential]"
        wait.until(EC.visibility_of_element_located(
            (By.CSS_SELECTOR, "select[name='{}']".format(element_name)))
        );
        driver.find_element_by_css_selector(".language-credential select option:last-of-type").click()
        driver.find_element_by_css_selector("input[name='submit']").click()
        # self.assertNotEqual("https://office.localhost/admin/interpreters",driver.current_url)
        wait.until(EC.visibility_of_element_located((By.ID, "hat")));
        error_divs = driver.find_elements_by_css_selector("#administrative .validation-error")
        self.assertGreater(len(error_divs),0)

    def test_something_else(self):
        driver = self.driver
        self.assertEqual("https://office.localhost/admin/interpreters/add",driver.current_url)
        menu = select.Select(driver.find_element_by_id("hat"))
        menu.select_by_visible_text("contract court interpreter")
        xpath =  "//option[text()='contract court interpreter']"
        option = driver.find_element_by_xpath(xpath)
        wait = WebDriverWait(driver, 3)
        wait.until(EC.element_to_be_selected(option))
        #script = '$("#interpreter-form").append(`<input name="interpreter[hat]" value="3">`);$("#interpreter-form").submit();'
        #driver.execute_script(script)
        # "/* $('#hat').val(3); */$('body').prepend(`<strong>WTF? hat is now ${$('#hat').val()}</strong>`)");
        #driver.find_element_by_xpath(xpath).click()
        driver.find_element_by_css_selector("input[name='submit']").click()


    # def test_form_submission_really_worked(self):
    #
    #     db = self.db
    #     cursor = db.cursor();
    #     query = "SELECT COUNT(*) FROM people WHERE email = 'gacker_boink@nysd.uscourts.gov'"
    #     cursor.execute(query)
    #     result = cursor.fetchone();
    #     # print("we got... ")print(result)


    def tearDown(self):

        #self.driver.close()
        pass


    @classmethod
    def tearDownClass(self):
        time.sleep(2)
        self.driver.close()
        #pass


if __name__ == "__main__":
    unittest.main()
