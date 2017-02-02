<?php
/** based closely on example from https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md */
namespace InterpretersOffice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $title;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $body;

    /**
     * @ORM\OneToMany(targetEntity="InterpretersOffice\Entity\Tag", mappedBy="blogPost",cascade={"persist"})
     */
    protected $tags;
    
    

    /**
     * Never forget to initialize all your collections !
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function addTags(Collection $tags)
    {
        foreach ($tags as $tag) {
            $tag->setBlogPost($this);
            $this->tags->add($tag);
        }
    }

    public function removeTags(Collection $tags)
    {
        foreach ($tags as $tag) {
            $tag->setBlogPost(null);
            $this->tags->removeElement($tag);
        }
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return BlogPost
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return BlogPost
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Add tag
     *
     * @param \InterpretersOffice\Entity\Tag $tag
     *
     * @return BlogPost
     */
    public function addTag(\InterpretersOffice\Entity\Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \InterpretersOffice\Entity\Tag $tag
     */
    public function removeTag(\InterpretersOffice\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }
}
