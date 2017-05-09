<?php

/**
 * module/InterpretersOffice/test/AbstractControllerTest.php.
 */

namespace ApplicationTest;

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Zend\Dom\Document;

/**
 * base class for unit tests.
 */
abstract class AbstractControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $configOverrides = [

            'module_listener_options' => [
                'config_glob_paths' => [
                    __DIR__.'/config/autoload/{{,*.}test,{,*.}local}.php',
                ],
            ],
        ];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__.'/../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();

    }

    /**
     * logs in a user through the AuthController.
     *
     * @param string $identity
     * @param string $password
     *
     * @return AbstractControllerTest
     */
    public function login($identity, $password)
    {
        //echo "\nentering ".__FUNCTION__."\n";
        $token = $this->getCsrfToken('/login','login_csrf');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'identity' => $identity,
                    'password' => $password,
                    'login_csrf' => $token,
                ]
            )
        );
        $this->dispatch('/login');
        
        $auth = $this->getApplicationServiceLocator()->get('auth');
        
        
         
        if (!$auth->hasIdentity()) {
            echo "\nWARNING:  failed authentication\n";
        } else {
           // echo "\nlogin IS OK !!!\n";
        }
       
        //var_dump($_SESSION);
        return $this;
    }
    //static $count = 0;
    /**
     * parses out a csrf token from a form.
     *
     * @param string $url  to dispatch
     * @param string $name name of the CSRF form element
     *
     * @return string $token parsed from document body
     */
    public function getCsrfToken($url, $name = 'csrf')
    {
        //$i = ++static::$count;
        // echo "\niteration: $i\n";
        //echo "\n url is $url\n";
        $this->dispatch($url, 'GET');
        $html = $this->getResponse()->getBody();
  
        //printf("\n$url html string length in %s: %d\n",__FUNCTION__,strlen($html));
        //$auth = $this->getApplicationServiceLocator()->get('auth');
        //printf("authenticated? %s, element name: $name\n",$auth->hasIdentity() ? "YES":"NO");
        $document = new Document($html,Document::DOC_HTML);
        
        $query = new Document\Query(); 
        //if ($name == 'csrf') { $selector = 'input'; } else {
            $selector = sprintf('input[name="%s"]', $name);
        //}
        /*
        if ('/admin/interpreters/add'==$url) {
           echo "parsing $url for: $selector ...";//           
           //#interpreter-form > div.form-group > div > input[type="hidden"]:nth-child(1)
        }         
         */
        $results = $query->execute($selector,$document,  Document\Query::TYPE_CSS);
        if (! count($results)) {
            echo $html; exit;
            throw new \Exception("fuck, could not parse out CSRF token!");
            return;
        }
        $node = $results->current();
        $token = $node->attributes->getNamedItem('value')->nodeValue;
        //echo "\n".__FUNCTION__." returning:   $token ....\n";
        return $token;
    }
}
