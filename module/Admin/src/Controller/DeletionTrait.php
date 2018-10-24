<?php
/** module/Admin/src/Controller/DeletionTrait.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\View\Model\JsonModel;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * attempts to delete an entity
 *
 * suitable for relatively simple entities, i.e., those other
 * than the Event entity
 *
 * @param $entity object
 * @param string $name
 * @return JsonModel
 */
trait DeletionTrait
{
    /**
     * deletes an entity
     *
     * @param  Array  $options
     * @return JsonModel
     */
    public function delete(array $options)
    {
        $entity = $options['entity'];
        $what = $options['what'];
        $name = $options['name'];
        $verbose_name = "The $what <strong>$name</strong>";
        $id = $options['id'];
        $xhr = $this->getRequest()->isXmlHttpRequest();
        if ($entity) {
            try {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
                //if (! $xhr) {
                $this->flashMessenger()
                    ->addSuccessMessage("$verbose_name has been deleted.");
                $redirect = true;
                //}
                $result = 'success';
                $error = [];
            } catch (ForeignKeyConstraintViolationException $e) {
                $result = 'error';
                $redirect = false;
                $error = [ 'message' =>
                    "Sorry &mdash; this $what cannot be deleted because there are other database records that refer to it.",
                    'code' => $e->getCode(),
                    'exception' => 'foreign_key_constraint',
                ];
                $this->getResponse()->setStatusCode(403);
            } catch (\Exception $e) {
                $result = 'error';
                $redirect = false;
                $error = [ 'message' =>
                    "Sorry, we hit an unexpected system error.",
                    'code' => $e->getCode(),
                    'exception' => get_class($e)
                ];
                $this->getResponse()->setStatusCode(500);
            }
        } else {
            $result = 'error';
            $error = ['message' => "$what $name (id $id) not found"];
            $redirect = true;
            $this->flashMessenger()
                  ->addWarningMessage("$verbose_name was not found.");
        }

        return new JsonModel(compact('result', 'error', 'redirect'));
    }
}
