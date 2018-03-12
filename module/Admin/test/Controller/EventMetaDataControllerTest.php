<?php
namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\FixtureManager;
use ApplicationTest\DataFixture;

use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Person;
use InterpretersOffice\Entity\InterpreterEvent;
use Zend\Stdlib\Parameters;

use Zend\Dom;

class EventMetaDataControllerTest extends AbstractControllerTest
{

    protected $dummy_data;

    public function setUp()
    {
        parent::setUp();
        FixtureManager::dataSetup();

        $this->login('david', 'boink');
        $this->reset(true);
    }

    protected function getDummyData()
    {
        if ($this->dummy_data) {
            return $this->dummy_data;
        }
        $data = [];
        $em = FixtureManager::getEntityManager();
        $judge = $em->getRepository(Entity\Judge::class)
                ->findOneBy(['lastname' => 'Dinklesnort']);
        $data['judge'] = $judge->getId();
        // $this->assertTrue(is_integer($data['judge']));
        $language = $em->getRepository(Entity\Language::class)
                ->findOneBy(['name' => 'Spanish']);
        $data['language'] = $language->getId();

        $data['date'] = (new \DateTime("next Monday"))->format("m/d/Y");
        $data['time'] = '10:00 am';
        $data['docket'] = '2017-CR-123';
        $type = $em->getRepository(Entity\EventType::class)->findOneBy(['name' => 'conference']);
        $data['eventType'] = $type->getId();
        $location = $em->getRepository(Entity\Location::class)
                ->findOneBy(['name' => '14B']);
        $data['location'] = $location->getId();
        $parent_location = $em->getRepository(Entity\Location::class)
                ->findOneBy(['name' => '500 Pearl']);
        $data['parentLocation'] = $parent_location->getId();
        $data['submission_date'] = (new \DateTime('-1 day'))->format("m/d/Y");
        $data['submission_time'] = '9:43 am';//(new \DateTime('-5 minutes'))->format("g:i a");
        $clerk_hat = $em->getRepository(Entity\Hat::class)
                ->findOneBy(['name' => 'Law Clerk']);
        $data['anonymousSubmitter'] = $clerk_hat->getId();
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p '
                . ' WHERE p.email = :email';
        $user = $em->createQuery($dql)
            ->setParameters(['email' => 'jane_zorkendoofer@nysd.uscourts.gov'])
            ->getOneorNullResult();
        $data['submitter'] = $user->getPerson()->getId();
        $data['anonymousJudge'] = '';
        $data['is_anonymous_judge'] = '';
        $data['cancellationReason'] = '';
        $data['id'] = '';

        $this->dummy_data = $data;
        return $data;
    }

    public function testChangingInterpretersUpdatesModificationTime()
    {

        $em = FixtureManager::getEntityManager();
        $dql = 'SELECT e FROM '.Entity\Event::class . ' e ';
        $shit = $em->createQuery($dql)->getResult();
        $this->assertTrue(is_array($shit));

        $this->assertTrue(is_object($shit[0]));
        /** @var $event InterpretersOffice\Entity\Event */
        $event = $shit[0];
        $interpreter = $event->getInterpreterEvents()->getValues()[0]->getInterpreter();
        // sanity-check
        $this->assertTrue($interpreter->getLastname() == 'Mintz');
        $event->setModified(new \DateTime("-10 minutes"));
        $old_timestamp = $event->getModified();
        //$event->setComments("fuck you at: ".time());
        $auth = $this->getApplicationServiceLocator()->get('auth');
        $adapter = $auth->getAdapter(); //john_somebody@nysd.uscourts.gov
        $adapter->setIdentity('david@davidmintz.org')
            ->setCredential('boink')
            ->authenticate();
        $em->flush();

        //$ievents = $event->getInterpreterEvents();
        // take David Mintz off the case
        //$ievents->removeElement($ievents->getValues()[0]);
        //$em->flush();

        // try adding a new Interpreter
        $margarita = $em->createQuery('SELECT i FROM '.Entity\Interpreter::class . ' i '.
            ' WHERE i.lastname = \'Somebody\'')->getResult()[0];
        //printf("\nMargarita is a %s\n",get_class($margarita));

        $shit = new Entity\InterpreterEvent( $margarita, $event );
        $shit->setCreatedBy($em->find(Entity\User::class,$auth->getStorage()->read()->id));
        $event->getInterpreterEvents()->add($shit);
        $em->flush();
        $this->assertTrue($event->getModified() > $old_timestamp);
        printf("\nold timestamp was:  %s\nnew timestamp is: %s\n",
            $old_timestamp->format('Y-m-d H:i:s'),
            $event->getModified()->format('Y-m-d H:i:s')
        );
    }

}
