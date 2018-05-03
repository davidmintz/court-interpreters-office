<?php
namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

/**
 * LocationsLoader
 *
 * loads both location-types and some locations
 */
class Locations extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $objectManager)
    {

        foreach (['courthouse','courtroom','holding cell','interpreters office',
                'jail','Pretrial Services office','public area',
                'US Probation office',]
        as $type) {
                ${$type} = new Entity\LocationType();
                ${$type}->setType($type)->setComments('');
                $objectManager->persist(${$type});
        }
        foreach (['pearl'=>'500 Pearl','foley'=>'40 Foley'] as
            $varname => $label) {
            $location = new Entity\Location();
            $location->setName($label)->setParentLocation(null)
                ->setType(${'courthouse'})->setComments('');
            $$varname = $location;
            $objectManager->persist($location);
        }
        $data = [
            ['MCC', ${'jail'}, null],
            ['MDC', ${'jail'}, null],
            ['7th floor', ${'US Probation office'}, ${'pearl'}],
            ['5th floor', ${'Pretrial Services office'},${'pearl'}],
            ['4th floor', ${'holding cell'},${'pearl'}],
            ['618', ${'courtroom'}, ${'foley'}],
            ['23A', ${'courtroom'}, ${'pearl'}],
            ['11A', ${'courtroom'}, ${'pearl'}],
            ['15A', ${'courtroom'}, ${'pearl'}],
            ['15B', ${'courtroom'}, ${'pearl'}],
            ['15C', ${'courtroom'}, ${'pearl'}],
            ['15D', ${'courtroom'}, ${'pearl'}],
            ['14A', ${'courtroom'}, ${'pearl'}],
            ['14B', ${'courtroom'}, ${'pearl'}],
            ['14C', ${'courtroom'}, ${'pearl'}],
            ['14D', ${'courtroom'}, ${'pearl'}],
            [ '5A', ${'courtroom'}, ${'pearl'}],
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
