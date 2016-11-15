<?php
/**
 * module/Application/src/Controller/IndexController.php.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  IndexController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class IndexController extends AbstractActionController
{
    /**
     * service manager.
     *
     * @var ContainerInterface
     */
    //protected $serviceManager;

    /**
     * constructor.
     *
     * @see Application\Controller\Factory\IndexControllerFactory
     *
     * @param ContainerInterface $serviceManager
     */
    
    protected $formFactory;

    protected $em;
    
    public function __construct($formFactory, $em)
    {
        //$this->serviceManager = $serviceManager;
        $this->formFactory = $formFactory;
        $this->em = $em;
    }


    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        // a little test
        try {
            
            $form = $this->formFactory->createForm(\Application\Entity\Language::class,[
                'action' => 'create',
                'object' => new \Application\Entity\Language()
            ]);
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates that we can build a form from annotations
     * and bind the form to a Doctrine entity, then add more elements
     */
    public function testAction()
    {
        $service = new \Zend\Authentication\AuthenticationService;
        //echo get_class($shit)," ... ";
        if ($service->hasIdentity()) {
            //require('module/Application/src/Entity/Role.php');
            
        
            $shit = $service->getStorage()->read();
        
            echo gettype($shit). "  is the type returned by read() ...<br>";
            echo  "YES identity ";
            $id = $service->getIdentity()->getId();
            //$user = $shit;
            $user = $this->em->find('Application\Entity\User',$id);
            echo gettype($user). " is the data type... ";
            echo get_class($user);
            echo "...",$user->getUsername();
            $person = $user->getPerson();
        
            echo " last name is ",$person->getLastname(), " role is ",
                $user->getRole()->getName(), " hat is ",$person->getHat();
        } else {
            echo " NOT authenticated... ";
        }
            
      
        
        $em = $this->em;
        //$thing = new \Application\Form\TestFieldset();
        //$thing->setObjectManager($em);
        // http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
        $builder = new  \Zend\Form\Annotation\AnnotationBuilder($this->serviceManager->get('entity-manager'));
        // could not get validators to run when person stuff was added as a
        // fieldset.
        /*
        $fieldset   = $builder->createForm(\Application\Entity\Person::class);
        $form = new \Zend\Form\Form('whatever');
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $form->add($fieldset);
        $fieldset->setUseAsBaseFieldset(true);
        */
        //http://stackoverflow.com/questions/29335878/zend-framework-2-form-issues-using-doctrine-as-a-hydrator
        //  you should invoke setHydrator() on form itself after adding the base fieldset.
        
        $form = $builder->createForm(\Application\Entity\Person::class);
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        // the firstname, middlename and lastname elements have already been
        // added and configured.
        // this demonstrates that we can add more after the fact
        $element = new \DoctrineModule\Form\Element\ObjectSelect('hat',
        [
                    'object_manager' => $em,
                    'target_class' => 'Application\Entity\Hat',
                    'property' => 'name',
                    'label' => 'hat',
                    'display_empty_item' => true,
        ]);
        $filter = $form->getInputFilter();
        //\Zend\Debug\Debug::dump(get_class_methods($filter));
        $filter->add([
            'name' => 'hat',
            'validators' => [
                [
                    'name' => 'Zend\Validator\NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'the shit is empty, yo!',
                        ],
                    ],
                ],
            ],
        ]);
        //https://docs.zendframework.com/zend-inputfilter/intro/
        $form->add($element);

        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //$language = new \Application\Entity\Language();
            $person = new \Application\Entity\Person();
            $form->bind($person);
            $form->setData($data);
            if (!$form->isValid()) {
                return $viewModel;
            }
            $em->persist($person);
            $em->flush();
            $this->flashMessenger()->addMessage('congratulations! you inserted an entity.');

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(['form' => $form]);
    }
    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        $form = new \Zend\Form\Form('person-form');
        $em = $this->serviceManager->get('entity-manager');
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em);
        $form->setHydrator($hydrator);

        $fieldset = new \Application\Form\PersonFieldset($em);
        $person = new \Application\Entity\Person();
        $fieldset->setObject($person)->setHydrator($hydrator);
        $form->add($fieldset);
        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($person);
            $form->setData($data);
            if (!$form->isValid()) {
                return $viewModel;
            } else {
                echo 'SHIT IS VALID ?!?';
                try {
                    $em->persist($person);
                    $em->flush();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }

                return $viewModel;
            }
        }

        return $viewModel;
    }
    /*
        $em      = $this->serviceManager->get('entity-manager');
        $form = new \Application\Form\Test($em);

        //echo "ok."; return new ViewModel;

        $viewModel = new ViewModel(['form' => $form]);
        $hat = new \Application\Entity\Hat();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($hat);
            $form->setData($data);
            if (! $form->isValid()) {
                return $viewModel;
            } else {
                echo "SHIT IS VALID ?!?";
                return $viewModel;
            }
        }



    */
    public function shitAction()
    {
        echo "shit is happening...<br>"; 
        $service = new \Zend\Authentication\AuthenticationService();
        $adapter = new \Application\Service\AuthAdapter([
            'object_manager' => $this->em,//'Doctrine\ORM\EntityManager',
            //'identity_class' => '\Application\Entity\User',
            // maybe change this to identity_properties, plural?
            //'identity_property' => 'email',
            'credential_property' => 'password',
            // 'credential_callable' => function (User $user, $passwordGiven) {
            //     return my_awesome_check_test($user->getPassword(), $passwordGiven);
            // },

            ]);
        $adapter->setIdentity('david')
                ->setCredential('boink');
        $service->setAdapter($adapter);
        $result = $service->authenticate();
        echo $result->isValid() ? "authentication success" : "auth FAILED.";
        //var_dump($result->getCode());
        
        return false; 
    }

}
namespace Application\Service\Authentication;

use Zend\Authentication\Result as AuthResult;

class Result extends AuthResult {
    const FAILURE_USER_ACCOUNT_DISABLED = -10;
}

namespace Application\Service;
//use Zend\Authentication\Adapter\AdapterInterface;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use Application\Service\Authentication\Result;

class AuthAdapter extends ObjectRepository
{

    public function __construct($options = [])
    {
        
        parent::__construct($options);
    }

   public function authenticate()
    {
        $this->setup();
        $options  = $this->options;
        $objectManager = $options->getObjectManager();
        $query = $objectManager->createQuery("SELECT u FROM Application\Entity\User u JOIN u.person p "
                . "WHERE p.email = :identity OR u.username = :identity")
                ->setParameters([':identity'=>$this->identity]);
        
        $identity = $query->getOneOrNullResult();
            // rather than:
            //->getObjectRepository()->findOneBy(array($options->getIdentityProperty() => $this->identity));

        if (!$identity) {            
            $this->authenticationResultInfo['code']       = \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

            return $this->createAuthenticationResult();
        }

        $authResult = $this->validateIdentity($identity);

        return $authResult;
    }
    
    protected function validateIdentity($identity) {
        //parent::validateIdentity($identity);
        $credentialProperty = $this->options->getCredentialProperty();
        $getter             = 'get' . ucfirst($credentialProperty);
        $documentCredential = null;

        if (method_exists($identity, $getter)) {
            $documentCredential = $identity->$getter();
        } elseif (property_exists($identity, $credentialProperty)) {
            $documentCredential = $identity->{$credentialProperty};
        } else {
            throw new Exception\UnexpectedValueException(
                sprintf(
                    'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                    $credentialProperty,
                    get_class($identity),
                    get_class($identity),
                    $getter
                )
            );
        }
        //$identity->getActive() ? " user is active ": " user is NOT active ";
        $credentialValue = $this->credential;
        $callable        = $this->options->getCredentialCallable();

        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        }

        if ($credentialValue !== true && $credentialValue !== $documentCredential) {
            $this->authenticationResultInfo['code']       = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }
        // this is in addition to the method we've overridden
        if (! $identity->isActive()) {
             $this->authenticationResultInfo['code']       = Result::FAILURE_USER_ACCOUNT_DISABLED;
             $this->authenticationResultInfo['messages'][] = 'User account is disabled (inactive).';
             
             return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code']       = Result::SUCCESS;
        $this->authenticationResultInfo['identity']   = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }
     /**
     * Creates a Application\Service\Authentication\Result object from the information 
     * that has been collected during the authenticate() attempt.
     *
     * @return Application\Service\Authentication\Result
     */
    protected function createAuthenticationResult()
    {
        return new Result(
            $this->authenticationResultInfo['code'],
            $this->authenticationResultInfo['identity'],
            $this->authenticationResultInfo['messages']
        );
    }

}