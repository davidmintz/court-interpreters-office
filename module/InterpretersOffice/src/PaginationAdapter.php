<?php
/**
 * just thinking about taking a stab at a pagination adapter
 * 
 */
namespace InterpretersOffice;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\Query;

/**
 * bla bla, so not ready for prime time
 */
class Paginator implements AdapterInterface
{
    /**
     * query
     * 
     * @var Query
     */
    protected $query;
    
    /**
     * constructor
     * 
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }
    
    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->query->setFirstResult($offset)->setMaxResults($itemCountPerPage);
        return $this->query->getResult();
    }
    
    /**
     * implements Countable
     */
    public function count()
    {
        $dql = $this->query->getDQL();
        echo "working with DQL: $dql";
        return 1;
    }
}
