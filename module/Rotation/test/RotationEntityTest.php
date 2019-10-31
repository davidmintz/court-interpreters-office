<?php
// module/Rotation/test/test1.php
//
use InterpretersOffice\Admin\Rotation\Entity;
use PHPUnit\Framework\TestCase;
use Zend\Test\PHPUnit\TestCaseTypeHintTrait;

$em = require __DIR__.'/../../../config/doctrine-bootstrap.php';

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
        $this->em = require __DIR__.'/../../../config/doctrine-bootstrap.php';
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
        $rotation->setStartDate(new \DateTime('2015-05-18'))
            ->setRotation('[881,840,862,199,198]')
            ->setTask($task);
        $em = $this->em;
        $em->persist($rotation);
        $em->persist($task);
        $em->flush();

        $this->assertEquals(++$count, $repo->count([]));

    }
}
