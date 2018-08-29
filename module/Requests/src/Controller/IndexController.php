<?php
/**
 * module/Requests/src/Controller/IndexController.php
 */

namespace InterpretersOffice\Requests\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Requests\Entity;


/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
    }


    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    public function testAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/index.phtml');


        return $view;
    }
    // $qb = $this->objectManager->createQueryBuilder();
    // $qb->select(['u'])->from('User','u')
    //->where('u.name ')
    //->where($qb->expr()->orX($qb->expr()->eq('u.firstName', '?1'),
    // $qb->expr()->LIKE('u.surname', '?2')))
    //->where($qb->expr()->orX($qb->expr()->lte('u.age', 40), 'u.numChild = 0'))
    //;
    //echo $qb->getDQL();

    public function listAction()
    {
        $repo = $this->objectManager->getRepository(Entity\Request::class);
        $paginator = $repo->list($this->auth->getIdentity());
        if ($paginator) {
            $ids = array_column($paginator->getCurrentItems()->getArrayCopy(),'id');            
            $defendants = $repo->getDefendants($ids);
        } else {
            $defendants = [];
        }
        $view = new ViewModel(['paginator' => $paginator,'defendants'=>$defendants ]);
        return $view;
    }
}
