<?php
/**
 * module/Requests/test/Controller/RequestsIndexControllerTest.php
 *
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Requests\Controller\RequestsIndexController;
use ApplicationTest\AbstractControllerTest;

/**
 * unit test for InterpretersOffice\Requests module's main controller
 */
class RequestsIndexControllerTest extends AbstractControllerTest
{

    public function testIndexCannotBeAccessedWithoutLogin()
    {
        $this->dispatch('/requests');
        $this->assertRedirect();
    }
}
