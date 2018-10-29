<?php /** module/InterpretersOffice/src/Controller/ExceptionHandlerTrait.php */

namespace InterpretersOffice\Controller;

use Zend\View\Model\JsonModel;

/**  handle controller action exceptions */
trait ExceptionHandlerTrait
{
    public function catch(\Throwable $e, $options = [])
    {

        $details = isset($options['details']) ? $options['details'] :'[none]';
        $this->events->trigger('error',$this,['exception'=>$e,
            'details'=>$details
        ]);
        $this->getResponse()->setStatusCode(500);

        return new JsonModel(['error'=>['message' => $e->getMessage(),]]);
    }
}
