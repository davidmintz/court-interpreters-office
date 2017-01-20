<?php
namespace InterpretersOffice\Form;

use InterpretersOffice\Entity\BlogPost;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class BlogPostFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('blog-post');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new BlogPost());

        $this->add([
            'type' => 'Zend\Form\Element\Text',
            'name' => 'title',
            'options' => ['label'=>'title',],
            
        ]);
        
        $this->add([
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'body',
             'options' => ['label'=>'body',],
        ]);
         $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
        ]);
        $tagFieldset = new TagFieldset($objectManager);
        $this->add([
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'tags',
            'options' => [
                'count'          => 2,
                'target_element' => $tagFieldset,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'title' => [
                'required' => true,
            ],
            'body' => [
                'required' => true,
            ],
        ];
    }
}

