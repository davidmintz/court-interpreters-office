<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * entity representing a judge "flavor"
 * 
 * for federal court, that means either USMJ or USDJ
 *  @ORM\Entity  @ORM\Table(name="judge_flavors")
 */
class JudgeFlavor {
    
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
    * @ORM\Column(type="string",length=60,options={"nullable":false})
    * @var string
    */
    protected $flavor;

    
}
