<?php

/** module/InterpretersOffice/src/Entity/LanguageRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom EntityRepository class for Language entity.
 */
class LanguageRepository extends EntityRepository
{
    use ResultCachingQueryTrait;

    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        $em->getConfiguration()->getResultCacheImpl()->setNamespace('languages');
        parent::__construct($em, $class);
    }

    /**
     * returns all languages wrapped in a paginator.
     *
     * @param int $page
     *
     * @return ZendPaginator
     */
    public function findAllWithPagination($page = 1)
    {
        $dql = 'SELECT language FROM InterpretersOffice\Entity\Language language ORDER BY language.name';
        $query = $this->createQuery($dql)->setMaxResults(30);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        if (!count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(30);

        return $paginator;
    }
    
    /**
     * gets all the languaages ordered by name ascending.
     *
     * @return array of all our LocationType objects
     */
    public function findAll()
    {
        // have the decency to sort them by name ascending 
        // and use the result cache
        $query = $this->createQuery(
            'SELECT l FROM InterpretersOffice\Entity\Language l ORDER BY l.name ASC'
        );
        
        return $query->getResult();
    }
}
