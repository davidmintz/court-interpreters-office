<?php

namespace ApplicationTest;

//use ApplicationTest\FixtureManager;
use InterpretersOffice\Entity;

trait DummyDataTrait
{

    protected $dummy_data;
   
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
        $data['event_type'] = $type->getId();
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
        $data['anonymous_submitter'] = $clerk_hat->getId();
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p '
                . ' WHERE p.email = :email';
        $user = $em->createQuery($dql)
            ->setParameters(['email' => 'jane_zorkendoofer@nysd.uscourts.gov'])
            ->getOneorNullResult();
        $data['submitter'] = $user->getPerson()->getId();
        $data['anonymous_judge'] = '';
        $data['is_anonymous_judge'] = '';
        $data['cancellation_reason'] = '';
        $data['id'] = '';

        $this->dummy_data = $data;
        
        return $data;
    }


}