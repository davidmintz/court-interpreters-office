<?php
/** module/Admin/src/Controller/DocketAnnotationsController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Validator\Csrf;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use InterpretersOffice\Admin\Service\DocketAnnotationService;

/**
 * handles create|update|delete actions
 */
class RestfulDocketAnnotationsController extends AbstractRestfulController
{

    /**
     * annotation service
     *
     * @var DocketAnnotationService
     */
    private $service;

    public function testAction()
    {
        return new JsonModel(['status'=>'test is working']);
    }

    /**
     * constructor
     *
     * @param DocketAnnotationService $service
     */
    public function __construct(DocketAnnotationService $service)
    {
        $this->service = $service;
    }

    /**
     * deletion
     *
     * @param  string $id
     * @return array
     */
    public function delete($id)
    {
        $headers = $this->getRequest()->getHeaders("X-Security-Token");
        $token = $headers ? $headers->getFieldValue():'';
        return new JsonModel($this->service->delete($id,$token));
    }

    /**
     * updates
     *
     * @param  string $id
     * @param  array $data
     * @return array
     */
    public function update($id, $data) {
        return new JsonModel($this->service->update($id,$data));
    }

    /**
     * creates
     *
     * @param  array $data
     * @return array
     */
    public function create($data)
    {
        return new JsonModel($this->service->create($data));
    }

    /**
     * gets number of events bearing docket number
     * 
     * @return JsonModel
     */
    public function countEventsAction()
    {
        $docket = $this->params()->fromQuery('docket');

        return new JsonModel(['count'=>$this->service->countEventsForDocket($docket)]);

    }
}
