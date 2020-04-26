<?php
/** module/Vault/test/Controller/InterpretersPersonalDataControllerTest.php */

namespace SDNY\Vault\Test\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;
use Laminas\Stdlib\Parameters;
//use Laminas\Dom\Document;
use InterpretersOffice\Entity;

use SDNY\Vault\Service\Vault;

/**
 * Description of InterpretersPersonalDataControllerTest
 *
 * @author david
 */
class InterpretersPersonalDataControllerTest extends AbstractControllerTest
{

    public function setUp()
    {
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
        $token = $this->getCsrfToken($url, 'csrf');
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
                'interpreterLanguages' => [
                    [
                        'language' => $russian->getId(),
                        'languageCredential' => '3',                        
                    ],
                ],
            ],
            'csrf' => $token,
        ];

        $this->getRequest()->setMethod('POST')->setPost(
            new Parameters($data)
        );
        $this->dispatch($url);
        $str = $this->getResponse()->getBody();
        $res = json_decode($str);
        $this->assertTrue(is_object($res));
        $this->assertEquals('success',$res->status);

        $id = $em->createQuery('SELECT i.id FROM InterpretersOffice\Entity\Interpreter i ORDER BY i.id DESC')
                ->setMaxResults(1)
                ->getSingleScalarResult();
        // select the entity back out
        $entity = $em->find('InterpretersOffice\Entity\Interpreter', $id);
        $encrypted_ssn = $entity->getSsn();
        $this->assertTrue(is_string($encrypted_ssn));
        $this->assertTrue($encrypted_ssn != $data['interpreter']['ssn']);

        $encrypted_dob = $entity->getDob();
        $this->assertTrue(is_string($encrypted_dob));
        $this->assertTrue($encrypted_dob != $data['interpreter']['dob']);
        //echo "\n", $entity->getInterpreterLanguages()->count(), " languages?\n";
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

        // without the Controller
        $former_ssn = $entity->getSsn();
        $former_dob = $entity->getDob();
        $entity->setSsn('987654321')->setDob('1968-06-18');
        $em->flush();

        $this->assertTrue($former_ssn != $entity->getSsn());
        $this->assertTrue($former_dob != $entity->getDob());
        $container = $this->getApplicationServiceLocator();
        $vault = $container->get(Vault::class);
        $decrypted_ssn = $vault->decrypt($entity->getSsn());
        $this->assertEquals('987654321', $decrypted_ssn);
        $decrypted_dob = $vault->decrypt($entity->getDob());
        $this->assertEquals('1968-06-18', $decrypted_dob);

        // with the controller
        $this->login('susie', 'boink');
        $this->reset(true);
        $id = $entity->getId();
        $url = "/admin/interpreters/edit/$id";
        $this->dispatch("/admin/interpreters/edit/$id");
        $token = $this->getCsrfToken($url, 'csrf');
        $russian = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'Russian']);

        $data = [
            'interpreter' => [
                'lastname' => 'Snertsky',
                'firstname' => 'David',
                'hat' => $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'contract court interpreter'])->getId(),
                'email' => 'snyert@example.org',
                'active' => 1,
                'id' => $entity->getId(),
                'dob' => '05/22/1971',
                'ssn' => '123456789',
                'language-select' => 1,
                'interpreterLanguages' => [
                    [
                        'language' => $russian->getId(),
                        'languageCredential' => 3,                        
                    ],
                ],
            ],
            'csrf' => $token,
        ];
         $this->getRequest()->setMethod('POST')->setPost(
             new Parameters($data)
         );
        $this->dispatch($url);
       //$this->dumpResponse();
        $res = $this->getResponse()->getBody();
        $data = json_decode($res);
        $this->assertTrue(is_object($data));
        $this->assertEquals('success',$data->status);
        // $this->assertRedirectTo('/admin/interpreters');
        $em->refresh($entity);
        $this->assertEquals('123456789', $vault->decrypt($entity->getSsn()));
    }
}
