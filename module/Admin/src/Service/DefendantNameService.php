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
    const USE_EXISTING_DUPLICATE = 'use_existing';
    const UPDATE_EXISTING_DUPLICATE = 'update_existing';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * attempts to update a defendant name
     * 
     * @param Entity\Defendant $entity
     * @param array $data the entity data
     * @param array $options extra data.  possible keys: 'event_id'...?
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
            return ['modified'=>false,'status'=>'success'];
        }
        // the first questions is whether there is a duplicate
        $duplicate = $this->findDuplicate($entity);
        $debug[] = "duplicate? ".($duplicate ? "yes":"no");
        $match = null;
        // and if so, whether it is exact or inexact
        if ($duplicate) {
            if ($this->isExactMatch($entity,$duplicate)) {
                $match = self::EXACT_DUPLICATE;    
            } else {
                $match = self::INEXACT_DUPLICATE;
            }
            $debug[] = "match: '$match'" ;
        }
       
        // and if it is inexact, whether they submitted a resolution policy:
        // use the existing one as is, or update the existing one        
        if ($match == self::INEXACT_DUPLICATE && ! ($data['duplicate_resolution']))
        {
            return [
                'status' => 'aborted',
                'inexact_duplicate_found' => true,
                'existing_entity' => [
                    'given_names' => $duplicate['given_names'],
                    'surnames'    => $duplicate['surnames'],
                 ],
            ];
        }        
        // now we need to know if the update is contextual or global
        $contexts_submitted = isset($data['contexts']) ? 
            array_map(function($i){ return json_decode($i, true);},$data['contexts']) :[];

        $all_contexts =  $this->em->getRepository(Entity\Defendant::class)
            ->findDocketAndJudges($entity->getId());
        
        $update_type =  $all_contexts == $contexts_submitted ? self::UPDATE_GLOBAL : self::UPDATE_CONTEXTUAL;
        $debug[] = 'type of update: '.$update_type;
        $db = $this->em->getConnection();
        switch ($duplicate) {
            case null:
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "no duplicate, doing global update";
                    $result = $this->em->transactional(function($db) use ($entity,$data) {
                        $entity->setGivenNames($data['given_names'])
                            ->setSurnames($data['surnames']);
                        return ['status'=>'success'];
                    });
                } else {
                    $debug[] = "no duplicate, contextual update";
                }
            break;

            case self::EXACT_DUPLICATE:
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "EXACT duplicate, global update";
                } else {
                    $debug[] = "EXACT duplicate, contextual update";
                }
            break;

            case self::INEXACT_DUPLICATE;
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "INEXACT duplicate, global update; dup resolution: " .$data['duplicate_resolution'];
                } else {
                    $debug[] = "INEXACT duplicate, contextual update; dup resolution: " .$data['duplicate_resolution'];
                }
            break;
        }

        return [
            'debug' => $debug,
            'status' => 'WIP', 
            'result' => $result ?? "TBD",          
            'duplicate_resolution' => $data['duplicate_resolution'],
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