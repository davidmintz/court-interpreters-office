<?php /* module/Requests/src/Entity/Request.php */

namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="InterpretersOffice\Requests\Entity\RequestRepository");
 * @ORM\Table(name="requests")
 */
class Request
{

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;




}
