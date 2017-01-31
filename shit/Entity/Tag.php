<?php
/** based closely on example from https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="InterpretersOffice\Entity\BlogPost", inversedBy="tags")
     */
    protected $blogPost;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Allow null to remove association
     */
    public function setBlogPost(BlogPost $blogPost = null)
    {
        $this->blogPost = $blogPost;
    }

    public function getBlogPost()
    {
        return $this->blogPost;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
