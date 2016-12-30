<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Admin\Controller\LanguagesController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use InterpretersOffice\Entity;

class LanguagesControllerTest extends AbstractControllerTest
{
    public function setUp()
    {

        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute([
            new DataFixture\LanguageLoader(),
            new DataFixture\MinimalUserLoader(),
            ]);
        $this->login('susie', 'boink');
    }

    public function testAddLanguage()
    {

        $entityManager = FixtureManager::getEntityManager();
        $repository = $entityManager->getRepository('InterpretersOffice\Entity\Language');
        $languages = $repository->findAll();
        $this->assertTrue(is_array($languages));
        $count_before = count($languages);
        $token = $this->getCsrfToken('/admin/languages/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'name' => 'Vulcan',
                    'comments' => 'rarely used',
                    'csrf' => $token,
                ]
            )
        );
        $this->dispatch('/admin/languages/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/languages');
        $count_after = count($repository->findAll());
        $this->assertEquals(1, $count_after - $count_before);

        $vulcan = $repository->findOneBy(['name' => 'Vulcan']);
        $this->assertInstanceOf(Entity\Language::class, $vulcan);

        return $vulcan;
    }

    /**
     * @depends testAddLanguage
     */
    public function testEditLanguage($vulcan)
    {
        $entityManager = FixtureManager::getEntityManager();
        $entityManager->persist($vulcan);
        $entityManager->flush();
        $id = $vulcan->getId();
        $comments_before = $vulcan->getComments();
        $this->dispatch('/admin/languages/edit/'.$id);
        //echo $this->getResponseHeader('Location');//return;
        $this->assertResponseStatusCode(200);
        $url = '/admin/languages/edit/'.$id;
        $token = $this->getCsrfToken($url);
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters(
                [
                    'name' => 'Vulcan',
                    'comments' => 'VERY rarely used',
                    'id' => $id,
                    'csrf' => $token,
                ]
            )
        );
        $this->dispatch($url);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/languages');
        $entityManager->refresh($vulcan);
        $this->assertNotEquals($comments_before, $vulcan->getComments());
    }

    public function testLanguagesIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/languages', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('interpretersoffice');
        // as specified in router's controller name alias...
        $this->assertControllerName(LanguagesController::class);

        $this->assertControllerClass('LanguagesController');
        $this->assertMatchedRouteName('languages');
    }
    public function testLanguagesEditActionCanBeAccessed()
    {
        $this->dispatch('/admin/languages/edit/1', 'GET', []);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('interpretersoffice');
        // as specified in router's controller name alias...
        $this->assertControllerName(LanguagesController::class);
        $this->assertMatchedRouteName('languages/edit');
    }
}
