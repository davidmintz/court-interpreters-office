<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Application\Entity;

class EventLoader implements FixtureInterface {
    
    public function load(ObjectManager $objectManager)
    {
        $date = new \DateTime("next monday");

        $time = new \DateTime('10:00 am');

        $judge = $objectManager->getRepository('Application\Entity\Judge')
                ->findOneBy(['lastname'=>'Failla']);

        $location = $judge->getDefaultLocation();

        $language = $objectManager->getRepository('Application\Entity\Language')
                ->findOneBy(['name'=>'Spanish']);

        $eventType = $objectManager->getRepository('Application\Entity\EventType')
                ->findOneBy(['name'=>'pretrial conference']);

        $comments = 'test one two';

        $dql = "SELECT u FROM Application\Entity\User u JOIN u.person p "
                . "WHERE p.email = 'john_somebody@nysd.uscourts.gov'";
        $query = $objectManager->createQuery($dql);
        $user = $query->getSingleResult();

        $interpreter = $objectManager->getRepository('Application\Entity\Interpreter')
                ->findOneBy(['lastname'=>'Mintz']);
        
        $defendant =  $objectManager->getRepository('Application\Entity\DefendantName')
                ->findOneBy(['surnames'=>'Fulano Mengano']);
        $event = new Entity\Event();
        $now = new \DateTime();              
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
            ->setModifiedBy($user)
            ->addInterpretersAssigned(
                 (new Entity\InterpreterEvent($interpreter,$event))->setCreatedBy($user)
             )
             ->addDefendant($defendant); 

        $objectManager->persist($event);

        $objectManager->flush();
        
        //$objectManager->remove($event);
        //$objectManager->flush();
        



    }
}



