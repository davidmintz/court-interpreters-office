<?php

/** module/Application/src/Form/View/Helper/FormElementErrors.php */

namespace Application\Form\View\Helper;

use Zend\Form\View\Helper\FormElementErrors as ZendFormElementErrors;

/**
 * class to override ZF3 FormElementErrors helper and use our own markup.
 */
class FormElementErrors extends ZendFormElementErrors
{
    /** @var string markup for closing the element */
    protected $messageCloseString = '</div>';

    /** @var string markup for opening the element */
    protected $messageOpenFormat = '<div%s class="alert alert-warning validation-error">';

    /** @var string markup for separating messages */
    protected $messageSeparatorString = '<br>';
}
