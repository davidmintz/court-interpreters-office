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
        $pdo = $objectManager->getConnection()->getWrappedConnection();
        $pdo->query('DELETE FROM requests WHERE event_id IS NOT NULL');
        $date = new \DateTime('next monday + 2 weeks');

        $time = new \DateTime('10:00 am');

        $judge = $objectManager->getRepository('InterpretersOffice\Entity\Judge')
                ->findOneBy(['lastname' => 'Daniels']);

        $location = $judge->getDefaultLocation();

        $language = $objectManager->getRepository('InterpretersOffice\Entity\Language')
                ->findOneBy(['name' => 'Spanish']);

        $event_type = $objectManager->getRepository('InterpretersOffice\Entity\EventType')
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
            ->setEventType($event_type)
            ->setDocket('2016-CR-0123')
            ->setComments('created by RequestLoader')
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
            ->setSubmissionDate($request->getCreated())
            ->setSubmissionTime($request->getCreated());
        $request->setEvent($event)->setPending(false);
        $objectManager->persist($event);
        $objectManager->persist($request);

        // create a Russian event and add to schedule AND assign an interpreter
        //    ['Snyertzski', 'Boris'],
        $russian = $objectManager->getRepository(\InterpretersOffice\Entity\Language::class)->findOneBy(['name'=>'Russian']);
        $russian_guy = $objectManager->getRepository(\InterpretersOffice\Entity\Defendant::class)->findOneBy(['surnames'=>'Snyertzski']);
        $date = new \DateTime('next Tuesday + 2 weeks');

        $russian_request = new Entity\Request();
        $russian_request->setDate($date)
        ->setTime($time)
        ->setJudge($judge)
        ->setLanguage($russian)
        ->setEventType($event_type)
        ->setDocket('2018-CR-0321')
        ->setComments('this is a Russian request created by data loader')
        ->setSubmitter($user->getPerson())
        ->setModified($then)
        ->setCreated($then)
        ->setLocation($location)
        ->setModifiedBy($user)
        ->setCreated(new \DateTime('-1 hour'))
        //->setSubmissionTime(new \DateTime('-1 hour'))
         ->addDefendant($russian_guy);

        $russian_event = new \InterpretersOffice\Entity\Event();
        foreach (['Date','Time','Judge','Language','Docket','EventType','Comments'] as $prop) {
            $russian_event->{'set'.$prop}($russian_request->{'get'.$prop}());
        }
        $russian_event->setCreated($recently)->setCreatedBy($admin)
            ->setModified($recently)->setModifiedBy($admin)
            ->setSubmissionDate($request->getCreated())
            ->setSubmissionTime($request->getCreated())
            ->setSubmitter($user->getPerson());
        $russian_interpreter = $objectManager->getRepository('InterpretersOffice\Entity\Interpreter')
            ->findOneBy(['email'=>'russian_interpreter@example.com']);
        $russian_request->setEvent($russian_event)->setPending(false);
        //$russian_event->addInterpreterEvents([$russian_interpreter);
        $collection = new ArrayCollection([
            (new \InterpretersOffice\Entity\InterpreterEvent($russian_interpreter, $russian_event))
                ->setCreatedBy($admin),
        ]);
        $russian_event->addInterpreterEvents($collection);
        $objectManager->persist($russian_event);
        $objectManager->persist($russian_request);


        $psi_request =  new Entity\Request();

        $uspo_user = $objectManager->createQuery(
            'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p WHERE p.email = :email'
        )->setParameters(['email'=>'john_probation@nysp.uscourts.gov'])->getOneorNullResult();

        $place = $objectManager->getRepository('InterpretersOffice\Entity\Location')->findOneByName('MDC');
        $defendant = $objectManager->getRepository('InterpretersOffice\Entity\Defendant')->findOneBySurnames('Franco');
        $judge =  $objectManager->getRepository('InterpretersOffice\Entity\Judge')->findOneByLastname('Noobieheimer');
        $type =  $objectManager->getRepository('InterpretersOffice\Entity\EventType')->findOneByName('probation PSI interview');
        $psi_request->setDate($date)
            ->setTime($time)
            ->setJudge($judge)
            ->setLanguage($language)
            ->setEventType($type)
            ->setDocket('2017-CR-1230')
            ->setComments('shit is real')
            ->setSubmitter($uspo_user->getPerson())
            ->setModified($then)
            ->setCreated($then)
            //->setSubmitter($user->getPerson())
            ->setLocation($place)
            ->setModifiedBy($uspo_user)
            ->setCreated(new \DateTime('-90 minutes'))
            //->setSubmissionTime(new \DateTime('-1 hour'))
             ->addDefendant($defendant);
        $objectManager->persist($psi_request);
        $objectManager->flush();

    }
}
