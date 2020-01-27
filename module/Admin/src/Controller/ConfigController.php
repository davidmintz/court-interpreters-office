<?php /** module/Admin/src/Controller/ConfigController.php */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;


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
        return ['config'=>$config];
    }

    private function post()
    {
        return new JsonModel([
            'status' => 'testing one two',
            'data' => $this->params()->fromPost()
        ]);
    }
}
