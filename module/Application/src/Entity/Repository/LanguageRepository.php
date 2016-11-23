<?php

/** module/Application/src/Entity/LanguageRepository.php */

namespace Application\Entity\Repository;

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
    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * returns all languages wrapped in a paginator.
     *
     * @todo rename, because it's misleading for findAll to return
     * a data type different from that of the method it overrides
     *
     * @param int $page
     *
     * @return ZendPaginator
     */
    public function findAllWithPagination($page = 1)
    {
        $dql = 'SELECT language FROM Application\Entity\Language language ORDER BY language.name';
        $query = $this->getEntityManager()->createQuery($dql)->setMaxResults(30);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        if (!count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(30);

        return $paginator;
    }
}
