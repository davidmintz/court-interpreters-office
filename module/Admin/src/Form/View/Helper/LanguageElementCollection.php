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
    
    protected $error_template = '<div class="alert alert-warning '
            . 'validation-error" style="display:%s">%s</div>';
	
    public function __invoke($collection)
	{
		//printf('<pre>%s</pre>',print_r(get_class_methods($collection),true));
        return $this->render($collection);
	}

	public function render($collection)
    {
        $form = $this->getView()->form;
        $element_collection = $form->get('interpreter')
                ->get('interpreterLanguages');
        $html = '';
        //echo $collection->count(), " is the \$collection->count()<br>";
        //echo $element_collection->count(), " is the \$element_collection->count()<br>";
        // we need these for reference
        $language_options = $this->getView()->form->get('interpreter')
                        ->get('language-select')->getValueOptions();
        
        /** INTERESTING FACT:  foreach DOES NOT ITERATE BEYOND THE 1st ELEMENT */
        for ($index = 0; $index < $element_collection->count(); $index++) { // as $index => $fieldset) {
            
            $fieldset = $collection->get($index);
            $hidden_element = $fieldset->get('language');
            $language = $hidden_element->getValue();
            if (!$language) {
                // because it's empty //echo "oops, no language?";
                return '';
            }
            $certification = $fieldset->get('federalCertification');

            if (is_object($language)) {

                // we were hydrated from an Interpreter entity
                $label = $this->view->escapeHtml($language->getName());
                $language_id = $language->getId();
                $certifiable = $language->isFederallyCertified();
                if ($certifiable) {
                    // convert possibly-boolean to int, to keep test from breaking
                    $value = $certification->getValue();
                    $certification->setValueOptions(
                            [-1 => '', 1 => 'yes', 0 => 'no']
                    );
                    $certification->setValue($value === true ? "1" : "0");
                } else {
                    $certification->setValue("-1")
                        ->setAttribute("disabled", "disabled");
                    //echo "we set cert to -1 at ".__LINE__ . "<br>";
                }
            } else {
                // form was populated with POST, not objects
                $language_id = $language;
                $key = array_search($language_id, array_column($language_options, 'value'));

                $label = $language_options[$key]['label'];
                // echo "\$key is $key, label $label ...<br>";
                $certifiable = $language_options[$key]['attributes']['data-certifiable'];
                //echo " and \$certifiable is: $certifiable ....";
                //if ('Spanish'==$label) {var_dump($certification->getValue());}
                if (!$certifiable) {
                    $certification->setValue("-1")
                            ->setAttribute("disabled", "disabled");
                    //echo "we set cert to -1 for $label at ".__LINE__ . "<br>";
                } else {
                    $certification->setValueOptions(
                            [-1 => '', 1 => 'yes', 0 => 'no']
                    );
                }
            }

            $hidden_element->setValue($language_id);
            $language_markup = $this->view->formElement($hidden_element);
            $language_markup .= $label;
            $certification->setAttribute('id', "fed-certification-$language_id");
            if (!$certifiable) {
                //echo "adding hidden for $label...";
                //$certification->setValue('-1')->setAttribute ("disabled","disabled");
                $certification_markup = $this->view->formElement($certification);
                $certification_markup .= sprintf(
                        '<input type="hidden" name="interpreter[interpreterLanguages]'
                        . '[%d][federalCertification]" value="-1">', $index);
            } else {
                $certification_markup = $this->view->formElement($certification);
            }
            // printf("iteration %d: now it's dark at %d<br>", $index, __LINE__);
            $messages = $collection->getMessages();
            if ($messages && $certifiable && -1 == $certification->getValue()) {
                $error_message = array_values($messages)[0];
                $errors = sprintf($this->error_template, 'block', $error_message);
                //$collection->setMessages
            } else {
                $errors = sprintf($this->error_template, 'none', '');
            }
            $html .= sprintf($this->template, $language_id, $language_markup, $language_id, $certification_markup, $errors);
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
            $certification_element
                    ->setValueOptions([-1 => '',1 => 'yes', 0 => 'no'])->setValue("-1");
            $certification_markup = $this->view->formSelect($certification_element);
        }
        $errors = sprintf($this->error_template,'none','');
        return sprintf($this->template, $language_id,
                $language_markup, $language_id,
                $certification_markup, $errors);
    }
}

