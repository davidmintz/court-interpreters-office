<?php /** module/Admin/src/Service/DefendantNameService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * insert|update|etc defendant names
 */
class DefendantNameService
{
    
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    const EXACT_DUPLICATE = 'exact';
    const INEXACT_DUPLICATE = 'inexact';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * find existing entity with same properties except id
     *
     * @param  Entity\Defendant $defendant
     * @return Defendant|null
     */
    public function findDuplicate(Entity\Defendant $defendant) :? Entity\Defendant
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\Defendant d
        WHERE d.given_names = :given_names
        AND d.surnames = :surnames ';//AND d.id <> :id';

        return $this->createQuery($dql)->setParameters([
            'given_names' => $defendant->getGivenNames(),
            'surnames' => $defendant->getSurnames(),
            //'id' => $defendant->getId()
        ])->getOneOrNullResult();
    }

    public function insert(Array $data) : Array    
    {
        $entity = new Entity\Defendant();
        $entity->setGivenNames($data['given_names'])
            ->setSurnames($data['surnames']);
        try {
            $this->em->persist($entity);
            $this->em->flush();
            return ['status'=>'success',
                'data'=>[
                    'id'=>$entity->getId(), 
                    'given_names' => $data['given_names'],
                    'surnames' => $data['surnames'],
                ]
            ];
        } catch (UniqueConstraintViolationException $e) {


        }
        return ['status'=>'WIP','data'=>$data];
    }

    public function getMatchType(Entity\Defendant $a, Entity\Defendant $b) :? bool
    {
        
    }

}