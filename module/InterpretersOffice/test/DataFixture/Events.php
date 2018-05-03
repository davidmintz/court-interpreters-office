<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use InterpretersOffice\Entity;

use Zend\Log;
use ApplicationTest\FakeAuth;

class Events implements FixtureInterface
{

    public function load(ObjectManager $objectManager)
    {

        $resolver = $objectManager->getConfiguration()->getEntityListenerResolver();
        $listener = new Entity\Listener\EventEntityListener();
        // to keep us from blowing up if we are outside the MVC context
        // or if the injection of things happens in an unfavorable sequence
        if (! $listener->getLogger()) {
            $logger = new Log\Logger();
            $logger->addWriter(new Log\Writer\Noop);
            $listener->setLogger($logger);
        }
        if (! $listener->getAuth() or ! $listener->hasIdentity()) {
            $user = $objectManager->getRepository(Entity\User::class)
                ->findOneBy(['username'=>'david']);
            if (! $user) {
                throw new \RuntimeException('no user david found in repo');
            }
            $listener->setAuth(new FakeAuth($user));
        }

        $resolver->register($listener);

        $date = new \DateTime('next monday');

        $time = new \DateTime('10:00 am');

        $judge = $objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Failla']);

        $location = $judge->getDefaultLocation();

        $language = $objectManager->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $eventType = $objectManager->getRepository('InterpretersOffice\Entity\EventType')
                ->findOneBy(['name' => 'pretrial conference']);

        $comments = 'test one two';

        $dql = "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
                ."WHERE p.email = 'john_somebody@nysd.uscourts.gov'";
        $query = $objectManager->createQuery($dql);
        $user = $query->getSingleResult();

        $interpreter = $objectManager->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);

        $defendant = $objectManager->getRepository('InterpretersOffice\Entity\DefendantName')
                ->findOneBy(['surnames' => 'Fulano Mengano']);
        $event = new Entity\Event();
        $now = new \DateTime();
        $collection = new ArrayCollection([
            (new Entity\InterpreterEvent($interpreter, $event))->setCreatedBy($user),
        ]);
        $event
            ->setDate($date)
            ->setTime($time)
            ->setJudge($judge)
            ->setLanguage($language)
            ->setEventType($eventType)
            ->setDocket('2016-CR-0123')
            ->setComments($comments)
            ->setAdminComments('')
            ->setSubmitter($user->getPerson())
            ->setModified($now)
            ->setCreated($now)
            ->setCreatedBy($user)
            ->setLocation($location)
            ->setModifiedBy($user)
             ->setSubmissionDate(new \DateTime('-1 hour'))
             ->setSubmissionTime(new \DateTime('-1 hour'))
             ->addDefendant($defendant)
             ->addInterpreterEvents(
                 $collection
             );

        $objectManager->persist($event);
        $objectManager->flush();

    }
}
