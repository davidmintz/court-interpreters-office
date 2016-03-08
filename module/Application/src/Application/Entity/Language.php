<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="languages") */

class Language 
{

	/**
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=50,nullable=false)
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string",length=200,nullable=false)
	 * @var string
 	 */
	protected $comments;




}