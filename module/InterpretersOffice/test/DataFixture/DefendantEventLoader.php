<?php
/** module/InterpretersOffice/test/DataFixture/DefendantEventLoader.php */
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Language;
use InterpretersOffice\Entity\EventType;
use InterpretersOffice\Entity\AnonymousJudge;

/**
 * DefendantEventLoader.
 *
 *  Depends on Judges, Users, Languages, Interpreters, etc
 */
class DefendantEventLoader implements FixtureInterface
{
    /**
     * loads defendant-event data
     *
     * rig it so that we can test updating a deft name that happens in one
     * context without disturbing it in other contexts when we don't intend
     * a global update
     *
     * @param  ObjectManager $objectManager [description]
     * @return [type]                       [description]
     */
    public function load(ObjectManager $objectManager)
    {
        $user_mintz = $objectManager->getRepository(Entity\User::class)->findOneBy(['username'=>'david']);
        $judge_repo = $objectManager->getRepository(Entity\Judge::class);
        $judge1 =  $judge_repo->findOneBy(['lastname'=>'Noobieheimer']);
        $judge2 =  $judge_repo->findOneBy(['lastname'=>'Dinklesnort']);
        $deft_repo =  $objectManager->getRepository(Entity\DefendantName::class);
        /*
        ['Rodríguez', 'José Luis'],
        ['Rodriguez', 'Jose'],
        ['Rodríguez Medina', 'Jose'],
         */
        $rodriguez_jose_luis = $deft_repo->findOneBy(['surnames'=>'Rodriguez','given_names'=>'José Luis']);

        $rodriguez_jose = $deft_repo->findOneBy(['surnames'=>'Rodriguez','given_names'=>'Jose']);

        $event_type_repo = $objectManager->getRepository(Entity\EventType::class);
        $conference = $event_type_repo->findOneBy(['name'=>'conference']);
        $plea    = $event_type_repo->findOneBy(['name'=>'plea']);
        $sentence = $event_type_repo->findOneBy(['name'=>'sentence']);
        $spanish = $objectManager->getRepository(Entity\Language::class)->findOneBy(['name'=>'Spanish']);
        // make 5 events with judge1
        $event1 = new Entity\Event();
        $then = new \DateTime('-123 days');
        $data = [
            [
                'date'=> new \DateTime('-120 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-122 days'),
                'submission_time'=> new \DateTime('-1 hours'),
            ],
            [
                'date'=> new \DateTime('-90 days'),
                'time' => new \DateTime('11:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-92 days'),
                'submission_time'=> new \DateTime('-1 hours'),
            ],
            [
                'date'=> new \DateTime('-60 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-61 days'),
                'submission_time'=> new \DateTime('-61 days -1 hours'),
            ],
            [
                'date'=> new \DateTime('-40 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $plea,
                'submission_date'=> new \DateTime('-41 days'),
                'submission_time'=> new \DateTime('-41 days -1 hours'),
            ],
            [
                'date'=> new \DateTime('-2 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $sentence,
                'submission_date'=> new \DateTime('-2 days'),
                'submission_time'=> new \DateTime('-2 days -1 hours'),
            ],
        ];
        foreach ($data as $i => $shit) {
            $entity = 'event'.$i;
            ${$entity} = new Entity\Event();
            $de = new Entity\DefendantEvent($rodriguez_jose,${$entity});
            ${$entity}
                ->setDate($shit['date'])
                ->setTime($shit['time'])
                ->setJudge($judge1)
                ->setLanguage($spanish)
                ->setEventType($shit['type'])
                ->setDocket('2016-CR-0123')
                //->setComments($comments)
                //->setAdminComments('')
                ->setSubmitter($user_mintz->getPerson())
                ->setModified($shit['submission_date'])
                ->setCreated($shit['submission_date'])
                ->setCreatedBy($user_mintz)
                //->setLocation($location)
                ->setModifiedBy($user_mintz)
                 ->setSubmissionDate($shit['submission_date'])
                 ->setSubmissionTime($shit['submission_time'])
                 ->addDefendantsEvents(new ArrayCollection([$de]));
            $objectManager->persist($de);
            $objectManager->persist(${$entity});
        }
        $other_data = [
            [
                'date'=> new \DateTime('-220 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-122 days'),
                'submission_time'=> new \DateTime('-1 hours'),
            ],
            [
                'date'=> new \DateTime('-190 days'),
                'time' => new \DateTime('11:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-192 days'),
                'submission_time'=> new \DateTime('-1 hours'),
            ],
            [
                'date'=> new \DateTime('-60 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $conference,
                'submission_date'=> new \DateTime('-161 days'),
                'submission_time'=> new \DateTime('-61 days -1 hours'),
            ],
            [
                'date'=> new \DateTime('-40 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $plea,
                'submission_date'=> new \DateTime('-141 days'),
                'submission_time'=> new \DateTime('-41 days -1 hours'),
            ],
            [
                'date'=> new \DateTime('-80 days'),
                'time' => new \DateTime('10:30 am'),
                'type' => $sentence,
                'submission_date'=> new \DateTime('-2 days'),
                'submission_time'=> new \DateTime('-2 days -1 hours'),
            ],

        ];
        foreach ($other_data as $i => $shit) {
            $entity = 'other_event'.$i;
            ${$entity} = new Entity\Event();
            $de = new Entity\DefendantEvent($rodriguez_jose,${$entity});
            ${$entity}
                ->setDate($shit['date'])
                ->setTime($shit['time'])
                ->setJudge($judge2)
                ->setLanguage($spanish)
                ->setEventType($shit['type'])
                ->setDocket('2015-CR-4321')
                //->setComments($comments)
                //->setAdminComments('')
                ->setSubmitter($user_mintz->getPerson())
                ->setModified($shit['submission_date'])
                ->setCreated($shit['submission_date'])
                ->setCreatedBy($user_mintz)
                //->setLocation($location)
                ->setModifiedBy($user_mintz)
                 ->setSubmissionDate($shit['submission_date'])
                 ->setSubmissionTime($shit['submission_time'])
                 ->addDefendantsEvents(new ArrayCollection([$de]));
            $objectManager->persist($de);
            $objectManager->persist(${$entity});
        }
      $magistrate = $objectManager->getRepository(Entity\AnonymousJudge::class)->findOneBy(['name'=>'magistrate']);
      $event_type_repo =  $objectManager->getRepository(Entity\EventType::class);
      $atty_client = $event_type_repo->findOneBy(['name'=>'attorney/client interview']);
      $presentment = $event_type_repo->findOneBy(['name'=>'presentment']);
      $anon_submitter =  $objectManager->getRepository(Entity\Hat::class)->findOneBy(['name'=>'Magistrates']);
      $magistrate_stuff = [
            [
              'date'=> new \DateTime(),
              'time' => new \DateTime('today 3:00 pm'),
              'type' =>  $atty_client,
              'submission_date'=> new \DateTime(),
              'submission_time'=> new \DateTime('-1 hours'),
            ],
            [
              'date'=> new \DateTime(),
              'time' => new \DateTime('today 4:45 pm'),
              'type' =>  $presentment,
              'submission_date'=> new \DateTime(),
              'submission_time'=> new \DateTime('-1 hours'),
            ],
      ];
      //['Rodríguez', 'Eusebio Morales']
      $eusebio = $deft_repo->findOneBy(['given_names'=>'Eusebio Morales']);
      foreach ($magistrate_stuff as $i => $shit) {
          $entity = 'mag_event'.$i;
          ${$entity} = new Entity\Event();
          $de = new Entity\DefendantEvent($eusebio,${$entity});
          ${$entity}
              ->setDate($shit['date'])
              ->setTime($shit['time'])
              ->setAnonymousJudge($magistrate)
              ->setLanguage($spanish)
              ->setEventType($shit['type'])
              ->setDocket('2018-MAG-4321')
              //->setComments($comments)
              //->setAdminComments('')
              ->setAnonymousSubmitter( $anon_submitter )
              ->setModified($shit['submission_date'])
              ->setCreated($shit['submission_date'])
              ->setCreatedBy($user_mintz)
              //->setLocation($location)
              ->setModifiedBy($user_mintz)
               ->setSubmissionDate($shit['submission_date'])
               ->setSubmissionTime($shit['submission_time'])
               ->addDefendantsEvents(new ArrayCollection([$de]));
          $objectManager->persist($de);
          $objectManager->persist(${$entity});
      }
      $objectManager->flush();
    }
}
