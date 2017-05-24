<?php
/**
 * module/Admin/test/Controller/PeopleControllerTest.php.
 */

namespace ApplicationTest\Controller;

use InterpretersOffice\Admin\Controller\PeopleController;
use ApplicationTest\AbstractControllerTest;
use Zend\Stdlib\Parameters;
use Zend\Dom\Query;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use InterpretersOffice\Entity;

class PeopleControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $fixtureExecutor->execute(
            [new DataFixture\MinimalUserLoader()]
        );

        
    }

    public function testAccessPeopleAdminPage()
    {
        $this->login('susie', 'boink');
        $this->reset(true);        
        $this->dispatch('/admin/people');
        $this->assertResponseStatusCode(200);
        //$this->assertModuleName('interpretersofficeadmin');
        $this->assertControllerName(PeopleController::class); // as specified in router's controller name alias
        $this->assertControllerClass('PeopleController');
        $this->assertMatchedRouteName('people');
    }

    public function testAdd()
    {
        $this->login('susie', 'boink');
        $this->reset(true);
       // first try a GET to check the form
        $this->dispatch('/admin/people/add');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form');
        $this->assertQuery('input#lastname');
        $this->assertQuery('input#firstname');
        $this->assertQuery('input#middlename');
        $this->assertQuery('input#email');
        $this->assertQuery('select#hat');
        $attorneyHat = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Hat')
                        ->findByName('defense attorney')[0]->getId();
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);        
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'email' => 'john.somebody@lawfirm.com',
                'active' => 1,
                'hat' => $attorneyHat,            ],
            'csrf' => $this->getCsrfToken('/admin/people/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        
        $this->dispatch('/admin/people/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/people');

        $query = FixtureManager::getEntityManager()->createQuery(
            'SELECT p FROM InterpretersOffice\Entity\Person p ORDER BY p.id DESC'
        );
        $entity = $query->setMaxResults(1)->getOneOrNullResult();
        $this->assertEquals('John', $entity->getFirstname());
        $this->assertEquals('Somebody', $entity->getLastname());

        return $entity;
    }
    /**
     * @depends testAdd
     *
     * @param
     */
    public function testDuplicateEntityValidation(Entity\Person $entity)
    {
        $em = FixtureManager::getEntityManager();
        $entity->setHat($em->getRepository('InterpretersOffice\Entity\Hat')
                ->findOneBy(['name' => 'defense attorney']));
        $em->persist($entity);
        $em->flush();
        $this->login('susie', 'boink');
        $this->reset(true);
        // try to add the same guy again
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'email' => 'john.somebody@lawfirm.com',
                'active' => 1,
                'hat' => $entity->getHat()->getId(),            ],
            'csrf' => $this->getCsrfToken('/admin/people/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertResponseStatusCode(200);
        $this->assertQuery('div.validation-error');
        $this->assertQueryCount('div.validation-error', 1);
        // a person with this &quot;Hat&quot; and email address is already in your database
        $this->assertQueryContentRegex('div.validation-error', '/person.+Hat.+email.+in your database/i');

        // different hat, but active and with same email...
        $hat = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Hat')
                        ->findByName('paralegal')[0];
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'email' => 'john.somebody@lawfirm.com',
                'active' => 1,
                'hat' => $hat->getId(),            ],
            'csrf' => $this->getCsrfToken('/admin/people/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertResponseStatusCode(200);
        $this->assertQuery('div.validation-error');
        $this->assertQueryCount('div.validation-error', 1);
        //echo $this->getResponse()->getBody();
        //return;
        $this->assertQueryContentContains(
            'div.validation-error',
            'there is already a person in your database with this email address and "active" setting'
        );
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);
        // now add another person, and edit the record to collide with John Somebody
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'email' => 'john.somebody.else@lawfirm.com',
                'active' => 1,
                'hat' => $hat->getId(),            ],
            'csrf' => $this->getCsrfToken('/admin/people/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
             new Parameters($data)
         );
        $this->dispatch('/admin/people/add');
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/people');

        $other_person = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Person')
                        ->findByEmail('john.somebody.else@lawfirm.com')[0];

        //$this->login('susie', 'boink');
        //$this->reset(true);
        $url = '/admin/people/edit/'.$other_person->getId();
        $data['person']['email'] = 'john.somebody@lawfirm.com';
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);
        $data['csrf'] = $this->getCsrfToken($url);
        $this->getRequest()->setMethod('POST')->setPost(
             new Parameters($data)
         );

        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertQuery('div.validation-error');
        //echo $this->getResponse()->getBody();
        $this->assertQueryCount('div.validation-error', 1);
        $this->assertQueryContentRegex('div.validation-error', '/person.+Hat.+email.+in your database/i');
    }
    /**
     * @depends testAdd
     *
     * @param Entity\Person
     */
    public function testEditAction(Entity\Person $person)
    {
        $em = FixtureManager::getEntityManager();
        $person->setHat($em->getRepository('InterpretersOffice\Entity\Hat')
                ->findOneBy(['name' => 'defense attorney']));
        $em->persist($person);
        $em->flush();
        $this->login('susie', 'boink');
        $this->reset(true);        
        $url = '/admin/people/edit/'.$person->getId();
        $this->dispatch($url);
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form');
        $this->assertQuery('input#lastname');
        $this->assertQuery('input#firstname');
        $this->assertQuery('input#middlename');
        $this->assertQuery('input#email');
        $this->assertQuery('select#hat');

        $query = new Query($this->getResponse()->getBody());
        $node1 = $query->execute('#lastname')->current();
        $lastname = $node1->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('Somebody', $lastname);

        $node2 = $query->execute('#firstname')->current();
        $firstname = $node2->attributes->getNamedItem('value')->nodeValue;
        $this->assertEquals('John', $firstname);
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John',
                'middlename' => 'Peter',
                'active' => 1,
                'hat' => $person->getHat()->getId(),            ],
            'csrf' => $this->getCsrfToken($url),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        $this->assertRedirect();
        $this->assertRedirectTo('/admin/people');
    }

    public function testFormValidation()
    {
        $attorneyHat = FixtureManager::getEntityManager()->getRepository('InterpretersOffice\Entity\Hat')
                        ->findByName('defense attorney')[0]->getId();
        $this->login('susie', 'boink');
        $this->reset(true);
        $data = [
            'person' => [
                'lastname' => 'Somebody',
                'firstname' => 'John (Killer)',
                'active' => 1,
                'hat' => $attorneyHat,            ],
            'csrf' => $this->getCsrfToken('/admin/people/add'),
        ];
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error', '/invalid characters/');

        $data['person']['lastname'] = '';
        $data['person']['firstname'] = 'John';
        $this->reset(true);
        $this->login('susie', 'boink');
        $this->reset(true);
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error', '/last name.+required/');

        $data['person']['lastname'] = 'Somebody';
        $data['person']['firstname'] = '';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error', '/first name.+required/');

        $data['person']['firstname'] = 'John';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');

        $data['person']['hat'] = '';
        $this->getRequest()->setMethod('POST')->setPost(
             new Parameters($data)
         );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error', '/hat.+required/');

        $data['person']['hat'] = $attorneyHat;
        /*
           we reconsider the following. for now, 'active' fields are not required,
           'allow_empty' is true, and Boolean filters cast the input values. otherwise
           Doctrine mistakenly thinks an update is necessary.
        */
        /*
        $data['person']['active'] = null;
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');

        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        echo $this->getResponse()->getBody(); return;
        $this->assertNotRedirect();
        
        $this->assertQuery('div.validation-error');
        $this->assertQueryContentRegex('div.validation-error', '/active.+required/');

        $data['person']['active'] = 'some random string';
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $this->getRequest()->setMethod('POST')->setPost(
             new Parameters($data)
         );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();

        $this->assertQueryCount('div.validation-error', 1);
        $this->assertQueryContentRegex('div.validation-error', '/invalid.+active/');
        */
        $data['person']['active'] = 1;
        $data['csrf'] = $this->getCsrfToken('/admin/people/add');
        $data['person']['hat'] = 'arbitrary shit';
        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch('/admin/people/add');
        $this->assertNotRedirect();
        $this->assertQueryCount('div.validation-error', 1);
        $this->assertQueryContentRegex('div.validation-error', '/invalid.+hat/');
    }
}
