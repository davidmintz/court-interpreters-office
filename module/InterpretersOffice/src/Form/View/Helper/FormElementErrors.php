<?php

/** module/InterpretersOffice/src/Form/View/Helper/FormElementErrors.php */

namespace InterpretersOffice\Form\View\Helper;

use Laminas\Form\View\Helper\FormElementErrors as LaminasFormElementErrors;

/**
 * class to override ZF3 FormElementErrors helper and use our own markup.
 */
class FormElementErrors extends LaminasFormElementErrors
{
    /** @var string markup for closing the element */
    protected $messageCloseString = '</div>';

    /** @var string markup for opening the element */
    protected $messageOpenFormat = '<div%s class="alert alert-warning validation-error">';

    /** @var string markup for separating messages */
    protected $messageSeparatorString = '<br>';
}
