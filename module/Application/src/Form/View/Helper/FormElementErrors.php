<?php
/** module/Application/src/Form/View/Helper/FormElementErrors.php */

namespace Application\Form\View\Helper;

use Zend\Form\View\Helper\FormElementErrors as ZendFormElementErrors;

/**
 * class to override ZF3 FormElementErrors helper and use our own markup.
 */
class FormElementErrors extends ZendFormElementErrors 
{

	protected $messageCloseString     = '</div>';
    protected $messageOpenFormat      = '<div%s class="alert alert-warning validation-error">';
    protected $messageSeparatorString = '<br>';

}