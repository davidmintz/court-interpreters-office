<?php
/** module/InterpretersOffice/src/View/Helper/DefendantNames.php */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View help for displaying defendant names
 *
 * @author david
 */
class DefendantNames extends AbstractHelper
{
    /**
     * defendant names
     *
     * @var Array
     */
    protected $defendants;

    /**
     * gets defendant names
     *
     * @return array
     */
    protected function getDefendants()
    {
        if ($this->defendants) {
            return $this->defendants;
        }
        $data = $this->getView()->data;
        if (! (is_array($data) && isset($data['defendants']))) {
            return false;
        }
        $this->defendants = $data['defendants'];

        return $this->defendants;
    }

    /**
     * Invokes this helper to display defendant names
     *
     * We presume a view variable $defendants, an array in the form
     * <code>
     * [ event_id =>
     *     [ 0=>
     *         [
     *              surnames=>"Some Surname",
     *              given_names=> "Given Names"
     *         ],
     *         ...
     *      ]
     *  ]
     * </code>
     * @param  int $id of event
     * @return string
     */
    public function __invoke($id)
    {
         $return = '' ;

        if (! $this->getDefendants() or ! isset($this->defendants[$id])) {
            return $return;
        }
        foreach ($this->defendants[$id] as $n) {
            $return .= $this->getView()->escapeHtml($n['surnames']).'<br>';
        }

         return $return;
    }
}
