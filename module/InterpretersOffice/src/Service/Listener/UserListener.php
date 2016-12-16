<?php 

namespace InterpretersOffice\Service\Listener;
use Zend\EventManager\Event;
class UserListener
{


    public function onTest(Event $e)
    {
        echo "Hello! This is the handler that fires on the ".$e->getName(). " event!<br>";
        
    }
}