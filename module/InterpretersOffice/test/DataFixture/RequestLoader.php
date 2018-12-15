<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InterpretersOffice\Requests\Entity;

/**
 * this depends on: Judges, Languages, EventTypes, Users, Interpreters,
 * Defendants having been loaded
 */
class RequestLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $date = new \DateTime('next monday + 2 weeks');

        $time = new \DateTime('10:00 am');

        $judge = $objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Daniels']);

        $location = $judge->getDefaultLocation();

        $language = $objectManager->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $eventType = $objectManager->getRepository('InterpretersOffice\Entity\EventType')
                ->findOneBy(['name' => 'pretrial conference']);

        $comments = 'created by RequestLoader';

        $dql = "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
                ."WHERE p.email = 'john_somebody@nysd.uscourts.gov'";
        $query = $objectManager->createQuery($dql);
        $user = $query->getSingleResult();

        $resolver = $objectManager->getConfiguration()->getEntityListenerResolver();
        // $interpreter = $objectManager->getRepository('InterpretersOffice\Entity\Interpreter')
        //         ->findOneBy(['lastname' => 'Mintz']);

        $defendant = $objectManager->getRepository('InterpretersOffice\Entity\Defendant')
                ->findOneBy(['surnames' => 'Fulano Mengano']);
        $request = new Entity\Request();
        $then = new \DateTime('-2 hours');

        $request
            ->setDate($date)
            ->setTime($time)
            ->setJudge($judge)
            ->setLanguage($language)
            ->setEventType($eventType)
            ->setDocket('2016-CR-0123')
            ->setComments($comments)
            ->setSubmitter($user->getPerson())
            ->setModified($then)
            ->setCreated($then)
            ->setSubmitter($user->getPerson())
            ->setLocation($location)
            ->setModifiedBy($user)
            ->setCreated(new \DateTime('-1 hour'))
            //->setSubmissionTime(new \DateTime('-1 hour'))
             ->addDefendant($defendant);


        $event = new \InterpretersOffice\Entity\Event();
        foreach (['Date','Time','Judge','Language','Docket','EventType','Comments'] as $prop) {
            $event->{'set'.$prop}($request->{'get'.$prop}());
        }
        $event->setSubmitter($user->getPerson())->addDefendant($defendant);
        $recently = new \DateTime('-1 hours');
        $admin = $objectManager->getRepository(\InterpretersOffice\Entity\User::class)->findOneBy(['username'=>'david']);
        $event->setCreated($recently)->setCreatedBy($admin)
            ->setModified($recently)->setModifiedBy($admin)
            ->setSubmissionDate($request->getCreated());
        $request->setEvent($event)->setPending(false);
        $objectManager->persist($event);
        $objectManager->persist($request);
        //try {
        $objectManager->flush();
        // } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
        //     echo "\noops. ",$e->getMessage(),"\n";
        // } catch (\Exception $e) {
        //     throw $e;
        // }

        //$request->setEvent($event);
        //$objectManager->flush();
        // $request_id = $request->getId();
        // printf("\nrequest id is %s\n",$request_id);
        // printf("\nevent id is? %s\n",$request->getEvent()->getId());
    }
}
