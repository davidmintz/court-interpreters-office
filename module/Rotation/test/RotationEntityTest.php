<?php
/** module/Rotation/test/RotationEntityTest.php */

use InterpretersOffice\Admin\Rotation\Entity;
use InterpretersOffice\Entity\Person;
use PHPUnit\Framework\TestCase;

/**
 * crude initial test to prove Doctrine wiring is OK
 *
 * should not be run against dev database unless you want to delete whatever
 * task-rotation data happens to be in there
 */
class RotationEntityTest extends TestCase
{

    /**
     * entity manager
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setUp()
    {
        // mysql development database
        $this->em = require __DIR__.'/../../../config/doctrine-bootstrap.php';
        // sqlite test database
        //$this->em = require __DIR__.'/../../InterpretersOffice/test/config/bootstrap.php';
        $this->em->createQuery('DELETE '.Entity\RotationMember::class. ' m')->getResult();
        $this->em->createQuery('DELETE '.Entity\Rotation::class. ' r')->getResult();
        $this->em->createQuery('DELETE '.Entity\Task::class. ' t')->getResult();

    }


    public function testCreateTask()
    {
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $count = $repo->count([]);
        $task = new Entity\Task();
        $task->setName('schedule management')
            ->setDescription('bla bla')
            ->setDuration('WEEK');
        $rotation = new Entity\Rotation();
        $rotation->setStartDate(new \DateTime('2015-05-18'))->setTask($task);

        $person_repo = $this->em->getRepository(Person::class);
        foreach ([881,840,862,199,198] as $order => $id) {
            $member = new Entity\RotationMember();
            $person = $person_repo->find($id);
            $member->setPerson($person)->setRotation($rotation)->setOrder($order);
            $rotation->addMember($member);
        }

        $em = $this->em;
        $em->persist($rotation);
        $em->persist($task);
        $em->flush();

        $this->assertEquals(++$count, $repo->count([]));

    }
}
