<?php /** module/Requests/src/Entity/Listener/RequestEntityListener.php */
namespace InterpretersOffice\Requests\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Log\LoggerAwareInterface;

use InterpretersOffice\Requests\Entity;
use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Service\Authentication\CurrentUserTrait;
use Doctrine\ORM\EntityManager;

/**
 * Request entity listener.
 */
class RequestEntityListener implements EventManagerAwareInterface, LoggerAwareInterface
{
    use Log\LoggerAwareTrait;
    use EventManagerAwareTrait;
    use CurrentUserTrait;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * sets Authentication service
     *
     * @param AuthenticationServiceInterface $auth
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    public function __construct()
    {
        //echo "WTF?";
    }

    /**
     * postLoad callback
     *
     * @param Entity\Event $request
     * @param LifecycleEventArgs $args
     */
    public function postLoad(
        Entity\Request $request,
        LifecycleEventArgs $args
    ) {

        $log = $this->getLogger();
        $log->debug("postload callback running in Request entity listener");
    }
}
