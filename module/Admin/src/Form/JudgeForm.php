<?php
/**
 * module/Admin/src/Form/JudgeForm.php
 */

namespace InterpretersOffice\Admin\Form;
use InterpretersOffice\Form\PersonForm;

use InterpretersOffice\Admin\Form;


/**
 * Description of JudgeForm
 *
 * @author david
 */
class JudgeForm extends PersonForm {
    
    protected $formName = 'judge-form';
    
    /**
     * name of Fieldset class to instantiate and add to the form.
     * 
     * @var string
     */
    protected $fieldsetClass = Form\JudgeFieldset::class;

    
    
}
