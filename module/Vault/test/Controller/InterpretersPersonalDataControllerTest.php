<?php
/** module/Vault/test/Controller/InterpretersPersonalDataControllerTest.php */

namespace SDNY\Vault\Test\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
//use Zend\Dom\Document;
use InterpretersOffice\Entity;

use SDNY\Vault\Service\Vault;


/**
 * Description of InterpretersPersonalDataControllerTest
 *
 * @author david
 */
class InterpretersPersonalDataControllerTest extends AbstractControllerTest {
    
    public function setUp() {
        parent::setUp();
        $fixtureExecutor = FixtureManager::getFixtureExecutor();

        $fixtureExecutor->execute(
                [
                    new DataFixture\MinimalUserLoader(),
                    new DataFixture\LanguageLoader(),
                    new DataFixture\InterpreterLoader(),
                ]
        );
        //echo "\nsetUp ran login()...\n";
        $this->login('susie', 'boink');  
        $this->reset(true);
    }
    
    public function testSetDobAndSsn()
    {
        
        if (! $this->getApplicationServiceLocator()->has(Vault::class)) {
            return;
        }
        $em = FixtureManager::getEntityManager();
        $url = '/admin/interpreters/add';
        $this->login('susie', 'boink');  
        $this->reset(true);
        $russian = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'Russian']);
        $token =  $this->getCsrfToken($url,'csrf'); 
        $data = [
            'interpreter' => [
                'lastname' => 'Snertsky',
                'firstname' => 'David',
                'hat' => $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'contract court interpreter'])->getId(),
                'email' => 'snyert@example.org',
                'active' => 1,
                'id' => '',
                'dob' => '05/22/1971',
                'ssn' => '123456789',
                'language-select' => 1,
                'interpreter-languages' => [
                    [
                        'language_id' => $russian->getId(),
                        'interpreter_id' => '',
                        'federalCertification' => '-1',
                    ],
                ],
            ],
            'csrf' => $token,
        ];
        
       $this->getRequest()->setMethod('POST')->setPost(
                new Parameters($data)
        );
        $this->dispatch($url);

        $this->assertRedirect();
        $this->assertRedirectTo('/admin/interpreters');
        
        $id = $em->createQuery('SELECT i.id FROM InterpretersOffice\Entity\Interpreter i ORDER BY i.id DESC')
                ->setMaxResults(1)
                ->getSingleScalarResult();
        // select the entity back out
        $entity = $em->find('InterpretersOffice\Entity\Interpreter',$id);        
        $encrypted_ssn= $entity->getSsn();
        $this->assertTrue(is_string($encrypted_ssn));
        $this->assertTrue($encrypted_ssn != $data['interpreter']['ssn']);
        
        $encrypted_dob= $entity->getDob();
        $this->assertTrue(is_string($encrypted_dob));
        $this->assertTrue($encrypted_dob != $data['interpreter']['dob']);
        
        return $entity;
        
    }
    /**
     * @depends testSetDobAndSsn
     * @param Entity\Interpreter $entity
     */
    public function testUpdateSsnAndDob(Entity\Interpreter $entity)
    {
        
        // TO BE CONTINUED !
        
        $em = FixtureManager::getEntityManager();
        $hat = $em->getRepository('InterpretersOffice\Entity\Hat')
                         ->findOneBy(['name' => 'contract court interpreter']);
        $entity->setHat($hat);
        $em->persist($entity); 
        $em->flush();
        //$container = $this->getApplicationServiceLocator();
        
        $this->login('susie', 'boink');  
        $this->reset(true);
        $id = $entity->getId();
        $url = "/admin/interpreters/edit/$id";
        $this->dispatch("/admin/interpreters/edit/$id");
        $token = $this->getCsrfToken($url,'login_csrf');
        echo "\n$token\n";
        //<input type="hidden" name="login_csrf" id="login_csrf" value="b0b4375e7de0397f342ae0f54cad36ea-97f3bfa1b7447eb2ab37fe84d597faab">       
        
        // $listener = $container->get('interpreter-listener');
        // echo "\n",$entity->getSsn(), "\n";
        $entity->setSsn('987654321');
        $this->assertTrue(true);
    }
}
