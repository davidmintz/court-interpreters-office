<?php
/** module/Rotation/test/RotationEntityTest.php */

use InterpretersOffice\Admin\Rotation\Entity;
use InterpretersOffice\Entity\Person;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;
use PHPUnit\Framework\TestCase;


/**
 * depends on our mysql dev database, ergo doesn't work without out it
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

        $task_name = 'Some Shit';
        $this->em = require __DIR__.'/../../../config/doctrine-bootstrap.php';
        $task = $this->em->createQuery('SELECT t FROM ' .Entity\Task::class. ' t WHERE t.name = :name')
        ->setParameters(['name'=>$task_name])->getOneOrNullResult();
        if ($task) {
            $rotations = $this->em->createQuery('SELECT r FROM '.Entity\Rotation::class. ' r
             JOIN r.task t WHERE t.name = :name')->setParameters(['name'=>$task_name])
             ->getResult();
             if ($rotations) {
                 foreach($rotations as $r) {
                    $this->em->remove($r);
                 }
             }
             $this->em->remove($task);
             $this->em->flush();
        }
    }


    public function testCreateTask()
    {
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $count = $repo->count([]);
        $task = new Entity\Task();
        $task->setName('Some Shit')
            ->setDescription('bla bla')
            ->setDuration('WEEK')->setFrequency('WEEK');
        $rotation = new Entity\Rotation();
        $rotation->setStartDate(new \DateTime('2015-05-18'))->setTask($task);

        $person_repo = $this->em->getRepository(Person::class);
        foreach ([881,840,862,199,198] as $order => $id) {
            $member = new Entity\RotationMember();
            $person = $person_repo->find($id);
            $member->setPerson($person)->setRotation($rotation)->setOrder($order);
            $rotation->addMember($member);
            $this->em->persist($member);
        }
        $task->addRotation($rotation);
        $em = $this->em;
        $em->persist($rotation);
        $em->persist($task);
        $em->flush();

        $this->assertEquals(++$count, $repo->count([]));

        //return $task;

    }

    public function __testFuckingSanity()
    {
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%scheduling%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        $this->assertTrue(is_object($task));
        $this->assertTrue($task instanceof Entity\Task);
        $rotations = $task->getRotations();
        $this->assertTrue($rotations instanceof \Doctrine\ORM\PersistentCollection);

        //printf("\nrotations has : %s\n",count($rotations));
        $r = $rotations->get(0);
        $this->assertTrue(is_object($r));
        $this->assertEquals('2015-05-18',$r->getStartDate()->format('Y-m-d'));
        //printf("\nrotation at index 0 has start date: %s\n",$r->getStartDate()->format('Y-m-d'));
        $example_date = new \DateTime('2019-11-06');
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $result = $repo->getDefaultAssignedPerson($task,$example_date);
        printf("\ngetDefaultAssignedPerson returns: %s\n",gettype($result));
        $this->assertTrue($result instanceof \InterpretersOffice\Entity\Person);
        $name = $result->getFullName();
        printf("\nfucking name is %s\n",$name);

    }
    /**
     *
     *
     */
    public function testGetDefaultSchedulingVictim()
    {
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%scheduling%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        $this->assertTrue($task instanceof Entity\Task);
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $example_date = new \DateTime('2019-11-06');
        $result = $repo->getDefaultAssignment($task,$example_date);
        $this->assertTrue(is_array($result),"expected \$assigned to be array, got ".gettype($result));
        $this->assertTrue($result['default'] instanceof Person, "expected instance of Person, got ".get_class($result['default']));
        $this->assertEquals("Paula",$result['default']->getFirstName(),'expected "Paula", got '.$result['default']->getFirstName());
        //printf("\ndate %s, assigned: %s\n",$example_date->format('D d-M-Y'),$assigned->getFirstName());
        // Mirta is default, Humberto is sub
        $example_date = new DateTime('2019-10-25');
        $default = $repo->getDefaultAssignment($task,$example_date);
        // printf("\ndate %s; default scheduler: %s\n",$example_date->format('D d-M-Y'),$default->getFirstName());
        $example_date = new DateTime('2019-10-25');
        $actual = $repo->getAssignment($task,$example_date);
        // printf("\ndate %s; default scheduler: %s\n",$example_date->format('D d-M-Y'),$actual['assigned']->getFirstName());
        $example_date = new DateTime('2019-12-01'); // a Sunday
        $actual = $repo->getAssignment($task,$example_date);
        // printf("\ndate %s; default scheduler: %s\n",$example_date->format('D d-M-Y'),$actual['assigned']->getFirstName());

    }

    public function testGetActualSchedulingVictimWhenSubstitutionOccurs()
    {
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%scheduling%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        $example_date = new DateTime('2019-10-25');
        $actual = $repo->getAssignment($task,$example_date);
        $this->assertEquals("Humberto",$actual['assigned']->getFirstName());
        $this->assertEquals("Mirta",$actual['default']->getFirstName());

        // again
        $example_date =  new DateTime('2019-06-11');
        $actual = $repo->getAssignment($task,$example_date);
        $this->assertEquals("Humberto",$actual['default']->getFirstName());
        $this->assertEquals("Mirta",$actual['assigned']->getFirstName());
    }

    public function testGetDefaultSaturdayVictim()
    {
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%saturday%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        $example_date = new \DateTime('2019-11-06');
        $default = $repo->getDefaultAssignment($task,$example_date);
        $this->assertEquals("Erika",$default['default']->getFirstName());

    }

    public function testGetActualSaturdayVictimWhenSubstitutionOccurs()
    {
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%saturday%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        $example_date = new DateTime('2019-11-02');
        $actual = $repo->getAssignment($task,$example_date);
        //printf("\ndate %s; actually assigned: %s\n",$example_date->format('D d-M-Y'),$actual['assigned']->getFirstName());
        $this->assertEquals("Mirta",$actual['default']->getFirstName());
        $this->assertEquals("Humberto",$actual['assigned']->getFirstName());

        $example_date = new DateTime('2019-10-30');
        $actual = $repo->getAssignment($task,$example_date);
        //printf("\ndate %s; actually assigned: %s\n",$example_date->format('D d-M-Y'),$actual['assigned']->getFirstName());
        $this->assertEquals("Mirta",$actual['default']->getFirstName());
        $this->assertEquals("Humberto",$actual['assigned']->getFirstName());

    }

    public function testRotationServiceCanCreateSubstitutionForSingleDayScheduling()
    {
        $service = new TaskRotationService($this->em, []);
        $this->assertTrue(is_object($service));
        // figure out the default whom we are overriding
        $date = new DateTime("next Monday +36 weeks");
        /** @var $repo InterpretersOffice\Admin\Rotation\Entity|RotationRepository */
        $repo = $this->em->getRepository(Entity\Rotation::class);
        $id = $this->em->createQuery('SELECT t.id FROM '.Entity\Task::class. ' t WHERE t.name LIKE :name')
            ->setParameters(['name'=>'%schedul%'])->getSingleScalarResult();
        $task = $repo->getTask($id);
        //$task =
        $assignment = $service->getAssignment($date->format('Y-m-d'),$id);
        $this->assertTrue(is_array($assignment));
        // $this->assertTrue(is_object($assignment['assigned']));
        $this->assertTrue(key_exists('assigned',$assignment), 'no key "assigned" in array returned by getAssignment()');
        $this->assertTrue(key_exists('default',$assignment), 'no key "default" in array returned by getAssignment()');
        // we assume. otherwise test can't continue
        $this->assertEquals($assignment['default']['id'],$assignment['assigned']['id']) ;
        $rotation = $assignment['rotation'];
        $default = $assignment['default']['id'];
        $person = null;
        // get first person who is NOT the default
        foreach ($rotation as $p) {
            if ($p['id'] != $default) {
                $person = $p['id'];
                break;
            }
        }
        // { date: "2020-12-15", task: 2, person: "198", duration: "DAY" }
        $csrf = (new \Zend\Validate\Csrf)->getHash();
        $post = [ 'date' => $date->format('Y-m-d'),'task' => 2, 'person' => 2, 'duration' => 'DAY', 'csrf'=> $csrf];
        // to do: CSRF!

    }
}
