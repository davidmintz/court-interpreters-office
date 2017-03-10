<?php /** */

namespace InterpretersOffice\Entity\Listener;

use  InterpretersOffice\Entity\Interpreter;

use Doctrine\ORM\Event\LifecycleEventArgs;

class InterpreterEntityListener
{

    public function postLoad(Interpreter $interpreter, LifecycleEventArgs $event)
    {
    	printf("shit is running in %s! yay!",__METHOD__);
	}
}