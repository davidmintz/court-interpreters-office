<?php
/** module/Application/src/Form/Factory/SimpleFormFactory.php */

namespace Application\Form\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

use Application\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use DoctrineModule\Validator\UniqueObject;
use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Annotation\AnnotationBuilder;




/**
 * 
 * Factory for creating Form objects for our relatively simple 
 * entities from the entities' annotations
 */
class AnnotatedEntityFormFactory implements FactoryInterface
{
    
    /**
     * {@inheritdoc}
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return type
     */
    public function __invoke(ContainerInterface $container, $requestedName, Array $options = null)
    {
        if (! class_exists($requestedName)) {
            throw new \DomainException(
                    'Factory cannot build a form from %s: class not found'
            );
        }
        $em = $container->get('entity-manager');
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($requestedName);
        
        switch ($requestedName) {
            case Entity\Language::class:
                if ($options['action']=='create') {
                    $validator = new NoObjectExistsValidator([
                       'object_repository' => $em->getRepository('Application\Entity\Language'),
                       'fields'            => 'name',
                       'messages' =>[NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this language is already in your database'],
                   ]);
                } else { // assume update
                    if (! $form->has('id')) {
                       echo "NO ID?!?";
                        //$form->add(['type'=>'Hidden','name'=>'id','value'=>$options['object']->getId()]);
                       
                    }
                    $validator = new UniqueObject([
                        'object_repository' => $em->getRepository('Application\Entity\Language'),
                        'object_manager' => $em,
                        'fields'        => 'name',
                        'use_context' => true,
                        'messages' =>[ UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'language name is not unique; already in your database'],
                    ]);
                }
                $input = $form->getInputFilter()->get('name');
                $input->getValidatorChain()
                   ->attach($validator);
        }
        $form->setHydrator(new DoctrineHydrator($em))
             ->setObject($options['object']);
        return $form;
    }
    
    function setupLanguageForm(Form $form,ObjectManager $em,Array $options)
    {
        if ($options['action']=='create') {
            $validator = new NoObjectExistsValidator([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'fields'            => 'name',
               'messages' =>[NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this language is already in your database'],
           ]);
       } else { // assume update
           if (! $form->has('id')) {
              // $form->add(['type'=>'Hidden','name'=>'id','value'=>$options['object']->getId()]);
           }
           $validator = new UniqueObject([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'object_manager' => $em,
               'fields'        => 'name',
               'use_context' => true,
               'messages' =>[ UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'language name is not unique; is already in your database'],
           ]);
       }
       $input = $form->getInputFilter()->get('name');
           $input->getValidatorChain()
          ->attach($validator);
    }
}
