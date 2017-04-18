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
class AbstractControllerTest extends AbstractHttpControllerTestCase
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
        return $this;
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
        $token = $this->getCsrfToken('/login');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'identity' => $identity,
                    'password' => $password,
                    'csrf' => $token,
                ]
            )
        );
        $this->dispatch('/login');
        
        $auth = $this->getApplicationServiceLocator()->get('auth');
        
        
         
        if (!$auth->hasIdentity()) {
            echo "\nWARNING:  failed authentication\n";
        } else {
            //echo "\nlogin IS OK !!!\n";
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
        
        //printf("\nhtml string length in %s: %d\n",__FUNCTION__,strlen($html));
        $auth = $this->getApplicationServiceLocator()->get('auth');
        //printf("authenticated? %s\n",$auth->hasIdentity() ? "YES":"NO");
        $document = new Document($html,Document::DOC_HTML);
        //$document->setStringDocument($html);
        $query = new Document\Query(); 
        $selector = sprintf('input[name="%s"]', $name);
        $results = $query->execute($selector,$document,  Document\Query::TYPE_CSS);
        $node = $results->current();
        $token = $node->attributes->getNamedItem('value')->nodeValue;
        //echo "\n".__FUNCTION__." returning:   $token ....\n";
        return $token;
    }
}
