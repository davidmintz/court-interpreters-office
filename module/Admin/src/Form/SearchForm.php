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
    public function __construct(ObjectManager $objectManager, array $options = [])
    {
        parent::__construct($objectManager, $options);
        // add more elements
    }

    public function init()
    {
        echo "this is init";
    }
}
