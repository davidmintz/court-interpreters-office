<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InterpretersOffice\Entity;

class EventLoader implements FixtureInterface
{
    
    const DUMMY_DOCKET = '2020-CR-0123';

    public function load(ObjectManager $objectManager)
    {
        $date = new \DateTime('next monday');

        $time = new \DateTime('10:00 am');

        $judge = $objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Failla']);

        $location = $judge->getDefaultLocation();

        $language = $objectManager->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $event_type = $objectManager->getRepository('InterpretersOffice\Entity\EventType')
                ->findOneBy(['name' => 'pretrial conference']);

        $comments = 'test one two';

        $dql = "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
                ."WHERE p.email = 'john_somebody@nysd.uscourts.gov'";
        $query = $objectManager->createQuery($dql);
        $user = $query->getSingleResult();

        $interpreter = $objectManager->getRepository('InterpretersOffice\Entity\Interpreter')
                ->findOneBy(['lastname' => 'Mintz']);

        $defendant = $objectManager->getRepository('InterpretersOffice\Entity\Defendant')
                ->findOneBy(['surnames' => 'Fulano Mengano']);
        $other_deft = $objectManager->getRepository('InterpretersOffice\Entity\Defendant')
        ->findOneBy(['surnames' => 'Franco']);
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
            ->setEventType($event_type)
            ->setDocket(self::DUMMY_DOCKET)
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
             ->addDefendant($defendant)->addDefendant($other_deft)
             ->addInterpreterEvents(
                 $collection
             );

        $objectManager->persist($event);
        $objectManager->flush();

        //$objectManager->remove($event);
        //$objectManager->flush();
    }
}
