<?php /** module/InterpretersOffice/src/Controller/ExceptionHandlerTrait.php */

namespace InterpretersOffice\Controller;

use Zend\View\Model\JsonModel;

/**
 * trait for exception-handling in controllers
 */
trait ExceptionHandlerTrait
{
    /**
     * exception handler for xhr requests
     *
     * @param  Throwable $e
     * @param  array     $options
     * @return JsonModel
     */
    public function catch(\Throwable $e, $options = [])
    {

        $details = isset($options['details']) ? $options['details'] : '[none]';
        $this->events->trigger('error', $this, ['exception' => $e,
            'details' => $details
        ]);
        $this->getResponse()->setStatusCode(500);
        return new JsonModel([
            'status' => 'error',
            'error' => ['message' => $e->getMessage(),]
        ]);
    }
}
