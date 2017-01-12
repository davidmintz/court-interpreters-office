<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\PersonForm;

/**
 *  ExampleController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class ExampleController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /**
     * fool around with person form and fieldset.
     */
    public function formAction()
    {
        echo 'shit works in formAction ... ';

        $form = new PersonForm($this->objectManager);

        $entity = new \InterpretersOffice\Entity\Person();

        $form->bind($entity);

        $form->setData([
            'person-fieldset' => [
                'firstname' => 'Wank',
                'lastname' => 'Gackersly',
                'email' => 'wank@gacker.com',
                'active' => 1,
                ],
            ]);
        echo 'valid? ';
        var_dump($form->isValid());
        echo '<br>',$entity->getEmail(), " is the entity's email...";

        $this->objectManager->persist($entity);

        try {
            //$this->objectManager->flush();
        } catch (\Exception  $e) {
            echo '<br>'.$e->getMessage();
        }
        $something = $form->getObject();

        echo get_class($something).' comes from $form->getObject()...';

        return false;
    }
/**
  * @return array
  */
 public function formCollectionAction()
 {
     $form = new \Application\Form\CreateProduct();
     $product = new \Application\Entity\Product();
     $form->bind($product);

     $request = $this->getRequest();
     $form->setAttribute('action',$request->getRequestUri());

     if ($request->isPost()) {
         $form->setData($request->getPost());

         if ($form->isValid()) {
            echo "<pre>";            
            var_dump($product);
            echo "</pre>";
         }
     }
     $viewModel = new ViewModel([
         'form' => $form,
     ]);
     $viewModel->setTerminal(true);
     return $viewModel;
 }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        echo 'it works';
        if ($this->events) {
            echo ' and this->events is a '.get_class($this->events);
            if ($sharedManager = $this->events->getSharedManager()) {
                echo ' ... and we have a shared manager! ... ';
            } else {
                echo ' but no shared event manager...';
            }
        }

        return false;

        return new ViewModel();
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates a way to trigger an event. the listener was attached
     * by the factory at instantiation.
     */
    public function testAction()
    {
        echo 'testAction works; ';
        echo '<br>note: i am '.self::class.'<br>';
       //$this->events->trigger("doShit",$this,["message" => "this is the message parameter"]) ;
        $this->events->trigger(
            __FUNCTION__,
            $this,
            ['message' => 'this is the message parameter']
        );

        return false;
    }

    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        
        $object = new \InterpretersOffice\Entity\Interpreter;
        $em = $this->objectManager;
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em);
        
        $data = [
           'lastname'  => 'Mintz',
            'firstname' => 'David',
            'email' => "david@example.com",
            'hat' => 1,
            'interpreterLanguages' => [
                ['language' => 62, 'interpreter'=> null ],
            ],
        ];
        $interpreter = $hydrator->hydrate($data, $object);
        
        

        return false;
    }
}

namespace Application\Entity;

class Product
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $price;

    /**
     * @var Brand
     */
    protected $brand;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Brand $brand
     * @return self
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Category[] $categories
     * @return self
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }
}

class Brand
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

class Category
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

//====================================================//


namespace Application\Form;

use Application\Entity\Brand;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

class BrandFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('brand');

        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setObject(new Brand());

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => 'Name of the brand',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'url',
            'type' => Element\Url::class,
            'options' => [
                'label' => 'Website of the brand',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => true,
            ],
        ];
    }
}

namespace Application\Form;

use Application\Entity\Category;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

class CategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('category');

        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setObject(new Category());

        $this->setLabel('Category');

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => 'Name of the category',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => true,
            ],
        ];
    }
}

namespace Application\Form;

use Application\Entity\Product;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

// not stdLib
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

class ProductFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('product');

        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setObject(new Product());

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => 'Name of the product',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        $this->add([
            'name' => 'price',
            'options' => [
                'label' => 'Price of the product',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        $this->add([
            'type' => BrandFieldset::class,
            'name' => 'brand',
            'options' => [
                'label' => 'Brand of the product',
            ],
        ]);

        $this->add([
            'type' => Element\Collection::class,
            'name' => 'categories',
            'options' => [
                'label' => 'Please choose categories for this product',
                'count' => 2,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => [
                    'type' => CategoryFieldset::class,
                ],
            ],
        ]);
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => true,
            ],
            'price' => [
                'required' => true,
                /*
                'validators' => [ // syntax error mixing array notation
                    [
                        'name' => 'Float',
                    ],
                ],
                */
            ],
        ];
    }
}

namespace Application\Form;


// error:  ClassMethods not in StdLib namespace


use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

//// syntax error - parent construct missing ;  

class CreateProduct extends Form
{
    public function __construct()
    {
        parent::__construct('create_product');

        $this->setAttribute('method', 'post');
        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setInputFilter(new InputFilter());

        $this->add([
            'type' => ProductFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'type' => Element\Csrf::class,
            'name' => 'csrf',
        ]);

        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Send',
            ],
        ]);
    }
}
