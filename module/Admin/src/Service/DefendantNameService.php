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
    const UPDATE_GLOBAL = 'global';
    const UPDATE_CONTEXTUAL = 'contextual';
    const USE_EXISTING_DUPLICATE = 'use existing';
    const UPDATE_EXISTING_DUPLICATE = 'update existing';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * attempts to update a defendant name
     * 
     * @param Entity\Defendant $entity
     * @param array $data the entity data
     * @param array $options extra data 
     * possible keys: 'existing_entity','duplicate_resolution','event_id'
     */
    public function update(Entity\Defendant $entity, array $data, array $options = [] ) : array
    {
        $debug = [];
        $modified = 0;
        foreach (['given_names','surnames'] as $prop) {
            if ($entity[$prop] != $data[$prop]) {
                $entity[$prop] = $data[$prop];
                $modified++;
            }
        }
        if (! $modified) {
            return ['modified'=>false,];
        }
        // the first questions is whether there is a duplicate
        $duplicate = $this->findDuplicate($entity);
        $debug[] = "duplicate? ".($duplicate ? "yes":"no");
        
        // and if so, whether it is exact or inexact
        if ($duplicate) {
            if ($this->isExactMatch($entity,$duplicate)) {
                $match = self::EXACT_DUPLICATE;    
            } else {
                $match = self::INEXACT_DUPLICATE;
            }
            $debug[] = "match: '$match'" ;
        }

        $contexts = $data['contexts'] ? 
        array_map(function($i){return json_decode($i);},$data['contexts']) :[];
        
        




        return [
            'debug' => $debug,
            'status' => 'WIP',
            'contexts' =>  $contexts,
            'data' => $data,
        ];
    }

    /**
     * attempts to insert a new defendant name 
     * 
     * @param array $data
     * @return array
     */
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
            $existing_entity = $this->findDuplicate($entity);
            
            return [
                'status' => 'error',
                'duplicate_entry_error' => true,
                'exact_match' => $this->isExactMatch($entity, $existing_entity),
                'existing_entity' => [
                    'surnames' => $existing_entity->getSurnames(),
                    'given_names' => $existing_entity->getGivenNames(),
                    'id' => $existing_entity->getId(),
                ],
            ];            
        }        
    }

    /**
     * 
     * 
     * @param Entity\Defendant $a
     * @param Entity\Defendant $b
     * @return bool true if match is exact (binary, literal)
     */
    public function isExactMatch(Entity\Defendant $a, Entity\Defendant $b) : bool
    {
        return $a->getGivenNames() == $b->getGivenNames()
            && $a->getSurNames() == $b->getSurnames();
    }

    /**
     * find existing entity with same properties
     *
     * @param  Entity\Defendant $defendant
     * @return Defendant|null
     */
    public function findDuplicate(Entity\Defendant $defendant) :? Entity\Defendant
    {
        $found = $this->em->getRepository(Entity\Defendant::class)->findOneBy([
            'given_names'=>$defendant['given_names'],
            'surnames'=>$defendant['surnames']
        ]);
        if (! $found) {
            return null;
        }
        if ($defendant->getId() && $found->getId() == $defendant->getId()) {
            // same object
            return null;
        }

        return $found;
    }
}