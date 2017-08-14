<?php

namespace ApplicationTest;

//use PHPUnit_Framework_TestCase;

use InterpretersOffice\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class FixtureSetupTest extends AbstractControllerTest
{
    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getApplicationServiceLocator()->get('entity-manager');
    }
    public function testSomething()
    {
        $container = $this->getApplicationServiceLocator();
        $this->assertTrue($container instanceof \Interop\Container\ContainerInterface);

        $objectManager = $this->getEntityManager();
        $connection = $objectManager->getConnection();
        $driver = $connection->getDriver();
        $this->assertEquals('pdo_sqlite', $driver->getName());
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
    }
    public function loadTestEventData()
    {
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        //$entityManager->getC
        //FixtureManager::start();
        $fixtureExecutor->execute([
            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\EventTypeLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\DefendantNameLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\CancellationReasonLoader(),
            new DataFixture\UserLoader(),
            new DataFixture\EventLoader(),
         ]);
    }
    public function testDataFixtureSanity()
    {
        $this->assertTrue(class_exists('ApplicationTest\FixtureManager'));
        $fixtureExecutor = FixtureManager::getFixtureExecutor();
        $this->assertTrue(is_object($fixtureExecutor));
        $entityManager = FixtureManager::getEntityManager();
       
        $fixtureExecutor->execute([
            new DataFixture\LanguageLoader(),
            new DataFixture\HatLoader(),
            new DataFixture\EventTypeLoader(),
            new DataFixture\LocationLoader(),
            new DataFixture\DefendantNameLoader(),
            new DataFixture\JudgeLoader(),
            new DataFixture\InterpreterLoader(),
            new DataFixture\CancellationReasonLoader(),
            new DataFixture\UserLoader(),
            new DataFixture\EventLoader(),
         ]);

        $this->assertTrue(is_object($entityManager));
        //echo get_class($entityManager);
        $languages = $entityManager->getRepository(Entity\Language::class)->findAll();
        $this->assertTrue(is_array($languages));
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = $entityManager->getConnection();
        $count = (int) $connection->fetchColumn('select count(*) from languages');

        $this->assertEquals($count, count($languages));
        
        $events = $entityManager->getRepository(Entity\Event::class)->findAll();
        /** @var $event InterpretersOffice\Entity\Event */
        $event = $events[0];
        $this->assertInstanceOf(Entity\Event::class, $event);
        $interpreters = $event->getInterpretersAssigned();
        $this->assertTrue($interpreters->count() >= 1);
        $assignment = $interpreters->current();
        $interpreter = $assignment->getInterpreter();
        $this->assertInstanceOf(Entity\Interpreter::class, $interpreter);
        $user = $assignment->getCreatedBy();
        $this->assertInstanceOf(Entity\User::class, $user);
        $defendants = $event->getDefendants();
        $this->assertTrue($defendants->count() >= 1);
        $defendant = $defendants->current();
        $this->assertInstanceOf(Entity\DefendantName::class,$defendant);
    }
    /**
     * test that a RuntimeException will be thrown if we try to persist an Event
     * with no Judge and no anonymous judge.
     */
    public function testExceptionThrownWhenNoJudgeOrAnonymousJudgeIsSet()
    {
        $this->loadTestEventData();
        $event = new Entity\Event();
        $date = new \DateTime('next monday');

        $time = new \DateTime('10:00 am');
        $objectManager = FixtureManager::getEntityManager();
        $language = $objectManager->getRepository(Entity\Language::class)
                ->findOneBy(['name' => 'Spanish']);

        $eventType = $objectManager->getRepository(Entity\EventType::class)
                ->findOneBy(['name' => 'pretrial conference']);

        $comments = 'test one two';

        $dql = "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
                ."WHERE p.email = 'john_somebody@nysd.uscourts.gov'";
        $query = $objectManager->createQuery($dql);
        $user = $query->getSingleResult();

        $interpreter = $objectManager->getRepository(Entity\Interpreter::class)
                ->findOneBy(['lastname' => 'Mintz']);

        $defendant = $objectManager->getRepository('InterpretersOffice\Entity\DefendantName')
                ->findOneBy(['surnames' => 'Fulano Mengano']);
        $event = new Entity\Event();
        $now = new \DateTime();
        //$judge = $objectManager->getRepository('InterpretersOffice\Entity\Judge')
        //        ->findOneBy(['lastname'=>'Failla']);
        $event
            ->setDate($date)
            ->setTime($time)
            ->setJudge(null)
            ->setLanguage($language)
            ->setEventType($eventType)
            ->setDocket('2016-CR-0123')
            ->setComments($comments)
            ->setAdminComments('')
            ->setSubmitter($user->getPerson())
            ->setModified($now)
            ->setCreated($now)
            ->setCreatedBy($user)
            ->setModifiedBy($user)
            ->addDefendant($defendant) 
            ->addInterpretersAssigned(
                    new ArrayCollection(
                       [     
                            (new Entity\InterpreterEvent($interpreter, $event))->setCreatedBy($user)
                       ]
                    )
                
            );
           //->setJudge($judge);

        $this->expectException(\RuntimeException::class);
        // this should suffice to throw a RuntimeException
        // and prove our lifecycle callback works
        $objectManager->persist($event);
    }

    public function testInsertInterpreter()
    {
        $this->loadTestEventData();
        $objectManager = FixtureManager::getEntityManager();

        // there should be an interpreter
        $interpreters = $objectManager
                ->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findAll();
        $this->assertGreaterThan(0, count($interpreters));

        $mintz = $objectManager
                ->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);

        $this->assertInstanceOf(Entity\Interpreter::class, $mintz);

        $languages = $mintz->getInterpreterLanguages();

        $this->assertGreaterThan(0, count($languages));
    }

    public function testAddAndRemoveInterpreterLanguage()
    {
        $this->loadTestEventData();
        $objectManager = FixtureManager::getEntityManager();
        $mintz = $objectManager
                ->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);

        $before = count($mintz->getInterpreterLanguages());
        //echo "\n";
        //$command = "echo 'select * from interpreters_languages;'  | sqlite3 module/InterpretersOffice/test/data/office.sqlite | wc -l && echo";
        //system($command);
        $this->assertEquals(1, $before);

        $french = $objectManager
                ->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'French']);
        $mintz->addInterpreterLanguage(
             new Entity\InterpreterLanguage($mintz, $french)
        );
        $objectManager->flush();
        $languages = $mintz->getInterpreterLanguages();
        $after = count($languages);
        $this->assertEquals(2, $after);

        //system($command);
        foreach ($languages as $obj) {
            if ($obj->getLanguage()->getName() == 'French') {
                $this_one = $obj;
                break;
            }
        }

        $this->assertEquals($before + 1, $after);
        $mintz->removeInterpreterLanguage($this_one);

        $objectManager->flush();

        $after = count($mintz->getInterpreterLanguages());

        $this->assertEquals($before, $after);
        // system($command);
    }
}
