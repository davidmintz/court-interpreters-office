exports.config = {
  tests: './*_test.js',
  output: './output',
  helpers: {
    // WebDriver: {
    //   url: 'https://office.localhost',
    //   browser: 'firefox'
    // }
    WebDriver: {
      url: 'http://localhost:8080',
      browser: 'firefox',
      host: '127.0.0.1',
      port: 4444,
      restart: false,
      windowSize: '1920x1680',
      desiredCapabilities: {
        chromeOptions: {
          args: [ /*"--headless",*/ "--disable-gpu", "--no-sandbox" ]
        }
      }
    }
  },
  include: {
    I: './steps_file.js'
  },
  bootstrap: null,
  mocha: {},
  name: 'codecept',
  plugins: {
    retryFailedStep: {
      enabled: true
    },
    screenshotOnFail: {
      enabled: true
    },
    autoLogin: {
      enabled: true,
      saveToFile: true,
      inject: 'login',
      users: {
        admin: {
          // loginAdmin function is defined in `steps_file.js`
          login: (I) => I.login(),
          // if we see `Admin` on page, we assume we are logged in
          check: (I) => {
             I.amOnPage('/');
             I.see('admin');
          }
        }
      }
    }
  }
}
