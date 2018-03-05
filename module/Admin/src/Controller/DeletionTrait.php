<?php
/** module/Admin/src/Controller/DeletionTrait.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\View\Model\JsonModel;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * attempts to delete an entity
 *
 * @param $entity object
 * @param string $name
 * @return JsonModel
 */
trait DeletionTrait
{
    public function delete(Array $options)
    {
        $entity = $options['entity'];
        $what = $options['what'];
        $name = $options['name'];
        $verbose_name = "The $what <strong>$name</strong>";
        $id = $options['id'];
        if ($entity) {
            try {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("$verbose_name has been deleted.");
                $result = 'success';
                $redirect = true;
                $error = [];
            } catch (ForeignKeyConstraintViolationException $e) {
                $result = 'error';
                $redirect = false;
                $error = [ 'message' =>
                    "This $what cannot be deleted because it has related database records",
                    'code' => $e->getCode(),
                    'exception' => 'foreign_key_constraint',
                ];
            }
        } else {
            $result = 'error';
            $error = ['message' => "$what $name (id $id) not found"];
            $redirect = true;
            $this->flashMessenger()
                  ->addWarningMessage("$verbose_name was not found.");
        }

        return new JsonModel(compact('result', 'error','redirect'));
    }
}
