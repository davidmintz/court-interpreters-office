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

    protected $previous_datetimes;

    protected $previous_defendants;

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
        $this->previous_datetimes = [
            'date'=>$request->getDate()->format("Y-m-d"),
            'time' => $request->getTime()->format('H:i'),
        ];
        $this->previous_defendants = $request->getDefendants()->toArray();
    }

    public function preUpdate(Entity\Request $request,PreUpdateEventArgs $args)
    {
        $changeset = $args->getEntityChangeSet();
        if ($this->previous_datetimes['time'] == $request->getTime()->format('H:i')) {
            unset($changeset['time']);
        }
        if ($this->previous_datetimes['date'] == $request->getDate()->format('Y-m-d')) {
            unset($changeset['date']);
        }
        
        if (count($changeset) or $this->defendantsWereModified($request)) {
            $this->getLogger()->debug("updating request meta in preUpdate listener");
            $request->setModified( new \DateTime())
                ->setModifiedBy($this->getAuthenticatedUser($args));
        }
    }


    private function defendantsWereModified(Entity\Request $request) {

        $now = $request->getDefendants()->toArray();
        $then = $this->previous_defendants;
        return $now != $then;

    }

}
