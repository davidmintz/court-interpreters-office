<?php /** module/Admin/src/Controller/ConfigController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;


use Laminas\InputFilter;
use Laminas\Validator;
use Laminas\Filter;

/**
 * configuration controller
 */
class ConfigController extends AbstractActionController
{
    public function indexAction()
    {
        return [];
    }

    private $config_path = 'module/Admin/config/forms.json';

    public function formsAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->post();
        }
        $error = false;
        if (! file_exists($this->config_path)) {
            $error = 'Unable to find form configuration file.';
        } elseif (! is_writable($this->config_path)) {
            $error = 'Form configuration file is not writeable.';
        } else {
            $data = file_get_contents($this->config_path);
            $config = json_decode($data);
            if (! $data) {
                $error = 'Form configuration file could not be parsed.';
            }
        }
        if ($error) {
            return ['errorMessage' => $error ];
        }
        return ['config'=> $config ];
    }

    public function getInputFilter()
    {
        $interpreterFormFilter = new InputFilter\InputFilter();
        foreach ([
        'BOPFormSubmissionDate', 'fingerprintDate','contractExpirationDate',
        'oathDate','securityClearanceDate'] as $field) {
            $interpreterFormFilter->add([
                'name' => $field,
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => ['isEmpty'=> "$field field is required"],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [ '0', '1' ],
                            'messages' => [
                                'notInArray' => "invalid value for $field"
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ]);
        }
        $eventFormFilter = new InputFilter\InputFilter();
        $field = 'end time';
        $eventFormFilter->add([
            'name'=>'endTime',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => ['isEmpty'=> "$field field is required"],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'InArray',
                    'options' => [
                        'haystack' => [ '0', '1' ],
                        'messages' => [
                            'notInArray' => "invalid value for $field"
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ]);
        $inputFilter = new InputFilter\InputFilter();
        $inputFilter->add($interpreterFormFilter,'interpreters');
        $inputFilter->add($eventFormFilter,'events');

        return $inputFilter;
    }

    private function post()
    {

        $inputFilter = $this->getInputFilter();
        $inputFilter->setData($this->params()->fromPost());
        if (! $inputFilter->isValid()) {
            return new JsonModel(['validation_errors' => $inputFilter->getMessages()]);
        }
        $data = $inputFilter->getValues();
        $array = [
            'interpreters' => ['optional_elements'=> $data['interpreters']],
            'events' => ['optional_elements'=> $data['events']],
        ];
        $json = json_encode($array,\JSON_PRETTY_PRINT);
        \file_put_contents($this->config_path,$json);
        return new JsonModel([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
