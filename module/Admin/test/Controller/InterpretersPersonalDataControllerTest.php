<?php
/** module/Application/test/Controller/InterpretersPersonalDataControllerTest.php */

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Zend\Stdlib\Parameters;
//use Zend\Dom\Document;
//use InterpretersOffice\Entity;

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
        
        
    }
    
}
