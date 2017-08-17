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
        } // else {   echo "\nlogin IS OK !!!\n";  }
       
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
       
        $this->dispatch($url, 'GET');
        $html = $this->getResponse()->getBody();
        $DEBUG = "\nGET: $url in getCsrfToken\n";
        $DEBUG .= "...parsing $name in getCsrfToken\n";
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $DEBUG .= sprintf("...authenticated? %s, element name: $name\n",$auth->hasIdentity() ? "YES":"NO");
        //echo "HTML in ".__FUNCTION__.":\n$html";
        $DEBUG .= sprintf("...$url html string length in %s: %d\n",__FUNCTION__,strlen($html));
        $DEBUG .= "is $name in HTML? "; 
        $DEBUG .= (boolean)strstr($html,"name=\"$name\"") ? "YES":"NO!";
        //echo "\n=================================\n";       
        $document = new Document($html,Document::DOC_HTML);        
        $query = new Document\Query();         
        $selector = sprintf('input[name="%s"]', $name);        
        $results = $query->execute($selector,$document,  Document\Query::TYPE_CSS);
        if (! count($results)) {           
            throw new \Exception("selector was $selector -- could not parse "
                    . "CSRF token! does the element exist? Is the HTML too deformed by error output?\nDEBUG: $DEBUG\n");            
        }
        $node = $results->current();
        $token = $node->attributes->getNamedItem('value')->nodeValue;
        //echo "\n".__FUNCTION__." returning:   $token ....\n";
        $this->reset(true);
        return $token;
    }
    
    public function dumpResponse()
    {
        echo $this->getResponse()->getBody();
    }
}
