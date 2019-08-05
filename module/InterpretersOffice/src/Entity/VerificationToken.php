<?php /** module/InterpretersOffice/src/Entity/VerificationToken.php  */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * storage for email verification and password reset tokens
 *
 * @ORM\Entity(readOnly=true)  @ORM\Table(name="verification_tokens")
 *
 */

class VerificationToken
{
    /**
     * id
     *
     * @ORM\Id @ORM\Column(type="string")
     */
    private $id;

    /**
     * token
     *
     * @ORM\Column(type="string")
     * @var string
     */
    private $token;

    /**
     * expiration
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $expiration;

    /**
     * Set id
     *
     * @param string $id
     *
     * @return VerificationToken
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return VerificationToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set expiration
     *
     * @param \DateTime $expiration
     *
     * @return VerificationToken
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * Get expiration
     *
     * @return \DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}
