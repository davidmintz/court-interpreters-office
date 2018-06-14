<?php

/** module/InterpretersOffice/test/DataFixture/HatLoader.php */

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

/**
 * loads test fixtures for "hats," roles, anonymous-judges.
 */
class HatLoader implements FixtureInterface
{
    /**
     * @see FixtureInterface
     *
     * @param ObjectManager $objectManager
     */
    public function load(ObjectManager $objectManager)
    {

        // create the Role entities
        foreach (['submitter', 'manager', 'administrator','staff'] as $roleName) {
            $role = new Entity\Role();
            $role->setName($roleName);
            $objectManager->persist($role);
        }
        $objectManager->flush();

        $submitter = $objectManager->getRepository('InterpretersOffice\Entity\Role')->findOneBy(['name' => 'submitter']);
        $manager = $objectManager->getRepository('InterpretersOffice\Entity\Role')->findOneBy(['name' => 'manager']);

        // create the Hat entities: name, can_be_anonymous, role
        $hats = [
            ['AUSA', 2, null,false],
            ['contract court interpreter', 0, null,false],
            ['Courtroom Deputy', 0, $submitter,true],
            ['defense attorney', 2, null,false],
            ['Law Clerk', 0, $submitter,true],
            ['paralegal', 2, null,false],
            ['Pretrial Services Officer', 0, $submitter,false],
            ['staff court interpreter', 0, $manager,false],
            ['Interpreters Office staff', 0, $manager,false],
            ['staff, US Attorneys Office', 2, null, false],
            ['USPO', 0, $submitter,false],
            ['Magistrates', 1, null,false],
            ['Pretrial', 1, null,false],
            ['Judge', 0, null,false],
        ];
        foreach ($hats as $hat) {

            $entity = new Entity\Hat();
            $entity->setName($hat[0])->setAnonymity($hat[1])
                ->setIsJudgeStaff($hat[3]);
            if ($hat[2]) {
                $entity->setRole($hat[2]);
            }
            $objectManager->persist($entity);
        }

        $objectManager->flush();

        // create the AnonymousJudge entities
        $anonymous_judges = ['Magistrate', 'not applicable', 'unknown'];

        foreach ($anonymous_judges as $j) {
            $entity = new Entity\AnonymousJudge();
            $entity->setName($j);
            $objectManager->persist($entity);
        }
        $objectManager->flush();

        $judge_flavors = ['USDJ' => 0, 'USMJ' => 5,'USBJ' => 10];
        foreach ($judge_flavors as $flavor => $weight) {
            $entity = new Entity\JudgeFlavor();
            $entity->setFlavor($flavor)->setWeight($weight);
            $objectManager->persist($entity);
        }
        $objectManager->flush();
    }
}
