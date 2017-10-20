<?php
namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class LanguageElementCollection extends AbstractHelper
{

	
	protected $template = <<<TEMPLATE
<div class="col-sm-offset-1 col-sm-3  interpreter-language language-name" id="language-%d">
%s
</div>
<div class="col-sm-5 form-inline interpreter-language language-certification">
    <label for="fed-certification-%d">fed-certified:</label>
          %s
   <button class="btn btn-danger btn-xs btn-remove-language" title="remove this language"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span class="sr-only">remove this language</span></button>
    %s
</div>

TEMPLATE;
    
    protected $error_template = '<div class="alert alert-warning validation-error" style="display:%s">%s</div>';
	
    public function __invoke()
	{
		return $this->render();
	}

	public function render()
	{
		$form = $this->getView()->form;
        $element_collection = $form->get('interpreter')
                ->get('interpreterLanguages');
        $html = '';
        // we need these for reference
        $language_options = $this->getView()->form->get('interpreter')
                        ->get('language-select')->getValueOptions();   
        foreach ($element_collection as $index => $fieldset) {
           
            $hidden_element = $fieldset->get('language');
            $language =  $hidden_element->getValue();
            $certification = $fieldset->get('federalCertification');
            
            if (is_object($language)) {
                // we were hydrated from an Interpreter entity
                $label = $this->view->escapeHtml($language->getName());
                $language_id = $language->getId();                
                $certifiable = $language->isFederallyCertified();
                if ($certifiable) {
                    // convert possibly-boolean to int, to keep test from breaking
                    $value = $certification->getValue()	;
                    $certification->setValue($value === true ? "1" : "0");
                    // and fix the N/A option !
                } else {
                    $certification->setValue("-1")
                        ->setAttribute ("disabled","disabled");
                    //echo "we set cert to -1 at ".__LINE__ . "<br>";
                } 
            } else {
                // form was populated with POST data, not objects
                $language_id = $language;
                $key = array_search($language_id,
                        array_column($language_options,'value'));
                $label =  $language_options[$key]['label'];
                $certifiable = $language_options[$key]['attributes']['data-certifiable'];
                //if ('Spanish'==$label) {var_dump($certification->getValue());}
                if (! $certifiable) {
                    $certification->setValue("-1")
                        ->setAttribute ("disabled","disabled");
                    //echo "we set cert to -1 for $label at ".__LINE__ . "<br>";
                }
            }          
            $hidden_element->setValue($language_id);
            $language_markup = $this->view->formElement($hidden_element);
            $language_markup .= $label;            
            $certification->setAttribute('id',"fed-certification-$language_id");            
            if (-1 == $certification->getValue()) {  
               //echo "adding hidden for $label...";
               //$certification->setValue('-1')->setAttribute ("disabled","disabled");
               $certification_markup = $this->view->formElement($certification);               
               $certification_markup .= sprintf(
                  '<input type="hidden" name="interpreter[interpreterLanguages]'
                       . '[%d][federalCertification]" value="-1">',
                   $index);
            } else {
                $certification_markup = $this->view->formElement($certification);
            }
            $errors = '<!-- error here -->';
            $html .= sprintf($this->template, $language_id,
            $language_markup, $language_id,
                $certification_markup,$errors);
            }
            
        return $html;
    
	}

	/**
	 *
	 *
	 */
    public function fromArray(Array $params)
    {
        $language = $params['language'];
        $i = $params['index'];
        $label = $this->view->escapeHtml($language->getName());
        $language_id = $language->getId();
        $language_markup = sprintf(
                  '<input type="hidden" name="interpreter[interpreterLanguages]'
                       . '[%d][language]" value="%d">',
                   $i,$language_id);
        $language_markup .= $label;
        $certification_element = new \Zend\Form\Element\Select(
                "interpreter[interpreterLanguages][$i][federalCertification]",
                ['value_options' => [
                            -1 => 'N/A',  
                            1 => 'yes',
                            0 => 'no',
                        ],               
                    'attributes' => [
                        'class' => 'form-control'
                    ]                    
                ]);
        $certification_element->setAttributes(['class'=>'form-control']);
        
        if (!  $language->isFederallyCertified()) {
            // disable element, append a hidden
            $certification_element->setAttribute('disabled','disabled');
            $certification_markup = $this->view->formSelect($certification_element);
            $name = "interpreter[interpreterLanguages][$i][federalCertification]";
            $certification_markup .= sprintf(
                '<input type="hidden" name="%s" value="-1">',$name);
        } else {
            $certification_markup = $this->view->formSelect($certification_element);
        }
        $errors = sprintf($this->error_template,'none','');
        return sprintf($this->template, $language_id,
                $language_markup, $language_id,
                $certification_markup, $errors);
    }
}

