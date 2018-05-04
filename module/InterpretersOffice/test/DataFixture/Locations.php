<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\LocationType;

/**
 * LocationsLoader
 *
 * loads both location-types and some locations
 */
class Locations extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

        //(new LocationTypes())->load($objectManager);

        $repo = $objectManager->getRepository(Entity\LocationType::class);
        $type_courthouse = $repo->findOneBy(['type'=>'courthouse']);;
        //$objectManager->flush();// does NOT HELP with:
        //1) ApplicationTest\Controller\EventControllerTest::testLoadEventInsertForm
        //Undefined index: 000000002cccb16c000000003f9d2447
        // which traces back to flush() in this method
        foreach (['500 Pearl','40 Foley'] as $name) {
            $location = new Entity\Location();
            $location->setName($name)->setParentLocation(null)
                ->setType($type_courthouse)->setComments('');
            $objectManager->persist($location);
        }
        $objectManager->flush();
        $jail = $repo->findOneBy(['type'=>'jail']);
        $courtroom = $repo->findOneBy(['type'=>'courtroom']);
        $locations = $objectManager->getRepository(Entity\Location::class);
        $pearl = $locations->findOneBy(['name'=>'500 Pearl']);
        $foley = $locations->findOneBy(['name'=>'40 Foley']);
        
        $data = [
            ['MCC', $jail, null],
            ['MDC', $jail, null],
            ['7th floor', $repo->findOneBy(['type'=>'US Probation office']), $pearl],
            ['5th floor', $repo->findOneBy(['type'=>'Pretrial Services office']),$pearl],
            ['4th floor', $repo->findOneBy(['type'=>'holding cell']),$pearl],
            ['23A', $courtroom, $pearl],
            ['618', $courtroom, $foley],
            ['11A', $courtroom, $pearl],
            ['15A', $courtroom, $pearl],
            ['15B', $courtroom, $pearl],
            ['15C', $courtroom, $pearl],
            ['15D', $courtroom, $pearl],
            ['14A', $courtroom, $pearl],
            ['14B', $courtroom, $pearl],
            ['14C', $courtroom, $pearl],
            ['14D', $courtroom, $pearl],
            [ '5A', $courtroom, $pearl],
        ];

        foreach ($data as $p) {
            $locationEntity = new Entity\Location();
            $locationEntity->setName($p[0])->setType($p[1])->setParentLocation($p[2])->setComments('');
            $objectManager->persist($locationEntity);
        }
        $objectManager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

}
