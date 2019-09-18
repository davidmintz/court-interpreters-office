<?php /** module/Admin/src/Form/SearchForm.php */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\AbstractSearchForm;
use InterpretersOffice\Entity;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * search form for admin users
 */
class SearchForm extends AbstractSearchForm
{
    /**
     * adds more elements
     *
     * @return SearchForm
     */
    public function init()
    {
        // add more elements!
        return $this;
    }

}
