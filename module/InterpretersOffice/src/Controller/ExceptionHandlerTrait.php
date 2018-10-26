<?php /** module/InterpretersOffice/src/Controller/ExceptionHandlerTrait.php */

namespace InterpretersOffice\Controller;

use Zend\View\Model\JsonModel;

/**  handle controller action exceptions */
trait ExceptionHandlerTrait
{
    public function catch(\Throwable $e, $options = [])
    {
        $this->getResponse()->setStatusCode(500);
        $this->events->trigger('error',$this,['exception'=>$e,
            //'details'=>'doing add event in Admin events controller'
            ]);
        $this->getResponse()->setStatusCode(500);

        return new JsonModel(['error'=>['message' => $e->getMessage(),]]);
    }
}
