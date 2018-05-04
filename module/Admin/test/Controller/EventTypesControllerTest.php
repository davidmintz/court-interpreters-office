<?php

namespace ApplicationTest\Controller;

use ApplicationTest\AbstractControllerTest;
use ApplicationTest\Bootstrap;
use ApplicationTest\DataFixture;

class EventTypesControllerTest extends AbstractControllerTest
{
    public function setUp()
    {
        parent::setUp();
        Bootstrap::load(

                [
                    new DataFixture\CancellationReasons(),
                    new DataFixture\Languages(),
                    new DataFixture\Roles(),
                    new DataFixture\Hats(),
                    new DataFixture\EventTypeCategories(),
                    new DataFixture\EventTypes(),
                    new DataFixture\DefendantNames(),
                    new DataFixture\LocationTypes(),
                    new DataFixture\Locations(),
                    new DataFixture\Judges(),
                    new DataFixture\Interpreters(),
                    new DataFixture\Users(),
            ]
        );

        $this->login('susie', 'boink');
        $this->reset(true);
    }

    public function testIndexAction()
    {
        $this->dispatch('/admin/event-types');
        $this->assertResponseStatusCode(200);

        $count = Bootstrap::getEntityManager()
            ->createQuery('SELECT COUNT(t.id) FROM InterpretersOffice\Entity\EventType t')
            ->getSingleScalarResult();
        $this->assertQuery('#event-types-list');
        $this->assertQueryCount('#event-types-list li', $count);
    }
}
