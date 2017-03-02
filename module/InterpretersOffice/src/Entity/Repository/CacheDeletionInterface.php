<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace InterpretersOffice\Entity\Repository;

/**
 *
 * @author david
 */
interface CacheDeletionInterface {
    
    public function deleteCache($cache_id = null);
    
}
