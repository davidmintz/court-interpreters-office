<?php

/** module/Admin/src/Controller/IndexController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * controller for admin/index.
 */
class AdminIndexController extends AbstractActionController
{
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $target = \InterpretersOffice\Admin\Service\Acl::class;
        /*
        echo "triggering some-shit event on $target..." ;
        $sharedEvents = $this->getEvent()->getApplication()->getServiceManager()->get('SharedEventManager');
        (new \Zend\EventManager\EventManager($sharedEvents))
                ->trigger($target,
                        "some-shit",
                        //$this,
                        ['message'=>'eat shit']);
        echo " ... wtf?";*/
        $this->getEventManager()->trigger('some-shit',$target,['foo'=>'gack']);
        $sharedEvents =  $this->getEventManager()->getSharedManager();
        
        //printf('<pre>%s</pre>',print_r($this->getEventManager()->getIdentifiers(),true));
        return new ViewModel(['title' => 'admin']);
    }
    
    public function __getEventManager() {
        if (! $this->events) {
            $container = $this->getEvent()->getApplication()->getServiceManager();
            $sharedEvents = $container->get('SharedEventManager');
            $this->events = new \Zend\EventManager\EventManager($sharedEvents);
        }
        return $this->events;
    }
    
    public function __setEventManager(\Zend\EventManager\EventManagerInterface $events) {
        
        parent::setEventManager($events);
        echo get_class($events),"<br>";
        //$events->setIdentifiers([\InterpretersOffice\Admin\Service\Acl::class]);
        $sharedEvents =  $this->getEventManager()->getSharedManager();
        echo "\$sharedEvents is: " ,gettype($sharedEvents),"<br>";
        $events->attach('some-shit',function($e){echo "Someshit happened involving {$e->getTarget()}!<br>";});
        //$this->events = $events;
        return $this;
    }
    
}
