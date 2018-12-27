<?php
/**
 *module/InterpretersOffice/src/View/Helper/InterpreterNames.php
 */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Description of InterpreterNames
 *
 * @author david
 */
class InterpreterNames extends AbstractHelper
{
    /**
     * interpreters indexed by event id
     *
     * @var array
     */
    protected $interpreters;

    /**
     * get the interpreters indexed by event id
     *
     * @return array
     */
    protected function getInterpreters()
    {
        if ($this->interpreters) {
            return $this->interpreters;
        }
        $data = $this->getView()->data;
        if (! (is_array($data) && isset($data['interpreters']))) {
            return [];
        }
        $this->interpreters = $data['interpreters'];

        return $this->interpreters;
    }

    /**
     * view helper
     *
     * @param int $id
     * @return string
     */
    public function __invoke($id)
    {
         $return = '' ;
        if (! $this->getInterpreters() or ! isset($this->interpreters[$id])) {
            return $return;
        }
        foreach ($this->interpreters[$id] as $n) {
            $return .= //$n['lastname'].'<br>';
                    sprintf('<div>%s<span class="d-none d-lg-inline">, %s</span></div>', $n['lastname'],$n['firstname']);
        }

         return $return;
    }
}
