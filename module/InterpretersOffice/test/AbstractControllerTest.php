<?php

/**
 * module/InterpretersOffice/test/AbstractControllerTest.php.
 */

namespace ApplicationTest;

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Dom\Query;

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
        }

        $this->reset(true);
    }

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
        $query = new Query($this->getResponse()->getBody());
        $selector = sprintf('input[name="%s"]', $name);
        $node = $query->execute($selector)->current();
        $token = $node->attributes->getNamedItem('value')->nodeValue;

        return $token;
    }
}
