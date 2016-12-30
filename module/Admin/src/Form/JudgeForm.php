<?php
/**
 * module/Admin/src/Form/JudgeForm.php
 */

namespace InterpretersOffice\Admin\Form;
use InterpretersOffice\Form\PersonForm;

use InterpretersOffice\Admin\Form;


/**
 * extends PersonForm
 * 
 * @see PersonForm
 * 
 */
class JudgeForm extends PersonForm {

	/**
	 * the name of our form
	 * 
	 * @var string 'judge-form'	 * 
	 */
    protected $formName = 'judge-form';
    
    /**
     * name of Fieldset class to instantiate and add to the form.
     * 
     * @var string
     */
    protected $fieldsetClass = Form\JudgeFieldset::class
    
}
