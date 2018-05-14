<?php

/** module/InterpretersOffice/src/Entity/LanguageRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\Language;

/**
 * custom EntityRepository class for Language entity.
 */
class LanguageRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    /**
     * @var string cache id prefix
     */
    protected $cache_id_prefix = 'languages:';

    /**
     * constructor
     *
     * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {

        parent::__construct($em, $class);
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace('languages');
    }


    /**
     * does entity $id have related entities?
     *
     * returns false if this Language has no related
     * entities and can therefore safely be deleted
     *
     * @param int $id the id of the language
     * @return boolean true if there are related entities
     */
    public function hasRelatedEntities($id)
    {
        $em = $this->getEntityManager();
        $event_count_dql = 'SELECT COUNT(e.id) FROM
            InterpretersOffice\Entity\Event e JOIN e.language l
            WHERE l.id = :id';
        $events = $em->createQuery($event_count_dql)->setParameters(['id'=>$id])
            ->getSingleScalarResult();
        if ($events) {
            return true;
        }
        $interp_lang_dql = 'SELECT COUNT(l.id) FROM
        InterpretersOffice\Entity\InterpreterLanguage il JOIN il.language l
            WHERE l.id = :id';
        $interpreters = $em->createQuery($interp_lang_dql)->setParameters(
            ['id'=>$id])->getSingleScalarResult();
        return $interpreters ? true : false;
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
        $dql = 'SELECT partial l.{id,name},
            SIZE(l.events) AS events, size(l.interpreterLanguages) AS interpreters
            FROM InterpretersOffice\Entity\Language l
            LEFT JOIN l.events e  LEFT JOIN e.interpreterEvents ie
            GROUP BY l.name ORDER BY l.name ASC';
        $query = $this->createQuery($dql)->setMaxResults(30);

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);
        if (! count($paginator)) {
            return null;
        }
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage(30);

        return $paginator;
    }

    /**
     * gets all the languages ordered by name ascending.
     *
     * @return array of all our Language objects
     */
    public function findAll()
    {
        // have the decency to sort them by name ascending,
        // and use the result cache
        $query = $this->createQuery(
            'SELECT l FROM InterpretersOffice\Entity\Language l ORDER BY l.name ASC'
            //, $this->cache_id_prefix . 'findAll'
        );

        return $query->getResult();
    }
    /**
     * returns all Languages for which there is federal certification
     *
     * @return Array
     */
    public function findAllCertifiedLanguages()
    {
        $query = $this->createQuery(
            'SELECT l.id, l.name FROM InterpretersOffice\Entity\Language l '
                . ' INDEX BY l.id '
                . ' WHERE l.name IN (:names)'
        )->setParameters([
            ':names' => ['Spanish','Haitian Creole','Navajo'],
        ]);

        return $query->getResult();
    }

    /**
     * experimental
     *
     * implements cache deletion
     * @param string $cache_id
     */
    public function deleteCache($cache_id = null)
    {
        $cache = $this->cache;
        $cache->setNamespace('languages');
        $cache->deleteAll();
    }
}
