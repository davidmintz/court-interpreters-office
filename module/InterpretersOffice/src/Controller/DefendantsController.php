<?php
/**
 * module/InterpretersOffice/src/Controller/DefendantsController.php
 */

namespace InterpretersOffice\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

use InterpretersOffice\Entity;

use InterpretersOffice\Form\Validator\ProperName;

/**
 *
 * for fetching defendant data for autocompletion, etc
 */
class DefendantsController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {

        $this->entityManager = $em;
    }

    /**
     * don't really need this. it can be removed
     *
     * @return \InterpretersOffice\Controller\ViewModel
     */
    public function indexAction()
    {
        return $this->getResponse()
            ->setContent("LocationsController/indexAction works");
    }

   /**
     * autocompletion for the defendant-name search box in
     * the interpreter-request form
     */
    public function autocompleteAction()
    {
        $repo = $this->entityManager->getRepository(Entity\Defendant::class);
        $term = $this->params()->fromQuery('term');
        $data = $repo->autocomplete($term);

        return new JsonModel($data);
    }

    /**
     * renders a LI element for defendant form
     *
     * this is for invoking via javascript/xhr
     *
     * @return ViewModel
     */
    public function renderAction()
    {
        $data = $this->params()->fromQuery();
        $view = new ViewModel($data);

        return $view->setTerminal(true)->setTemplate('partials/defendant');
    }

    /**
     * returns defendant-name search results
     *
     * @return ViewModel
     */
    public function searchAction()
    {
        $search = $this->params()->fromQuery('term');
        $repo = $this->entityManager->getRepository(Entity\Defendant::class);
        $paginator = $repo->paginate($search, $this->params()->fromQuery('page'));
        $viewModel = new ViewModel(['paginator' => $paginator,'search' => $search]);
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        }

        return $viewModel;
    }

    /**
     * validates given name(s) and surname(s)
     *
     * @return JsonModel
     */
    public function validateAction()
    {
        $data = $this->params()->fromPost();
        $filter = $this->getInputFilter();
        $filter->setData($data);
        if (! $filter->isValid()) {
            return new JsonModel([
                'valid' => false,
                'validation_errors' => $filter->getMessages(),
            ]);
        }

        return new JsonModel(['valid' => true]);
    }

    /**
     * creates and returns an InputFilter for a proper name
     *
     * @return \Laminas\InputFilter\InputFilter input filter for defendant name
     */
    protected function getInputFilter()
    {
        $filter = new \Laminas\InputFilter\InputFilter();
        $filter->add([
            'name' => 'given_names',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => ['messages' => ['isEmpty' => 'given (first) name is required']],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 2,
                        'max' => 60,
                        'messages' => [
                            'stringLengthTooShort' => 'minimum length is %min% characters',
                            'stringLengthTooLong' => 'maximum length is %max% characters',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => ProperName::class,
                    'options' => ['type' => 'first']
                ]
            ],
        ]);
        $filter->add([
            'name' => 'surnames',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => ['messages' => ['isEmpty' => 'surname (last name) is required']],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 2,
                        'max' => 60,
                        'messages' => [
                            'stringLengthTooShort' => 'minimum length is %min% characters',
                            'stringLengthTooLong' => 'maximum length is %max% characters',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => ProperName::class,
                    'options' => ['type' => 'last']
                ]
            ],
        ]);

        return $filter;
    }
}
