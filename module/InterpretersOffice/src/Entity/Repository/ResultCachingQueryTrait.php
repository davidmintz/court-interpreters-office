<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace InterpretersOffice\Entity\Repository;

/**
 * Description of ResultCachingQueryTrait
 *
 * @author david
 */
trait ResultCachingQueryTrait {
    
    function createQuery($dql='')
    {
       
        $query = $this->getEntityManager()->createQuery($dql);
        $query->useResultCache(true);
        return $query;

    }
    
}
