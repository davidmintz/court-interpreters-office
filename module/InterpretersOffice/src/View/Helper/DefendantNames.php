<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Description of DefendantNames
 *
 * @author david
 */
class DefendantNames extends AbstractHelper
{
     
    protected $defendants;
    
    protected function getDefendants()
    {
        if ($this->defendants) {
            return $this->defendants;
        }
        $data = $this->getView()->data;
        if (! (is_array($data) && isset($data['defendants']))) {
            return false;
        }
        $this->defendants = $data['defendants'];
        
        return $this->defendants;
            
    }
            
    public function __invoke($id)
     {
         $return = '' ;
         if (! $this->getDefendants() or ! isset($this->defendants[$id]))
         {
             return $return;
         }        
         foreach ($this->defendants[$id] as $n) {
             $return .= $n['surnames'].'<br>';
         }
         
         return $return;
     }
}
