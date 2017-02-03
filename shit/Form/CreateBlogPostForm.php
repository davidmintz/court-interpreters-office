<?php

namespace InterpretersOffice\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class CreateBlogPostForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-blog-post-form');

        // The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset
        $blogPostFieldset = new BlogPostFieldset($objectManager);
        $blogPostFieldset->setUseAsBaseFieldset(true);
        $this->add($blogPostFieldset);
        
        $this->add([
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
             'attributes' => ['value' => 'save','class'=>'btn btn-success btn-lg'],
        ]);

        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}