<?php /** module/Admin/src/Form/View/Helper/LanguageElementCollection.php */

namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * helper for displaying interpreter-languages
 */
class LanguageElementCollection extends AbstractHelper
{

    /**
     * html template
     *
     * @var string
     */
    protected $template = <<<TEMPLATE
<!-- shit begins -->
<div class="offset-sm-1 col-sm-3  interpreter-language language-name form-inline    " id="language-%d">
%s
</div>
<div class="col-sm-8 form-inline interpreter-language language-credential">
    <label for="languageCredential-%d">credential:</label>
          %s
   <button class="ml-3 btn btn-warning btn-xs btn-remove-language  border" title="remove this language"><span class="fas fa-times" aria-hidden="true"></span>
        <span class="sr-only">remove this language</span></button>
    %s
</div>
<!-- shit ends -->

TEMPLATE;

    /**
     * error template
     * @var string
     */
    protected $error_template = '<div class="alert alert-warning '
            . 'validation-error" style="display:%s">%s</div>';

    /**
     * proxies to render()
     *
     * @return string
     */
    public function __invoke()
    {
        return $this->render();
    }

    /**
     * renders interpreter-language elements
     *
     * @return string
     */
    public function render()
    {
        $form = $this->getView()->form;
        $collection = $form->get('interpreter')
                ->get('interpreterLanguages');
        $html = '';
        //echo $collection->count(), " is the \$collection->count()<br>";
        //echo $element_collection->count(), " is the \$element_collection->count()<br>";
        // we need these for reference
        $language_options = $this->getView()->form->get('interpreter')
                        ->get('language-select')->getValueOptions();

        /** INTERESTING FACT:  foreach DOES NOT ITERATE BEYOND THE 1st ELEMENT */
        for ($index = 0; $index < $collection->count(); $index++) { // as $index => $fieldset) {
            $fieldset = $collection->get($index);
            $hidden_element = $fieldset->get('language');
            $language = $hidden_element->getValue();
            if (! $language) {
                // because it's empty //echo "oops, no language?";
                return '';
            }
            $credential = $fieldset->get('languageCredential');

            if (is_object($language)) {
                // we were hydrated from an Interpreter entity
                $label = $this->view->escapeHtml($language->getName());
                $language_id = $language->getId();
                // $certifiable = $language->isFederallyCertified();
                // if ($certifiable) {
                //     // convert possibly-boolean to int, to keep test from breaking
                //     $value = $credential->getValue();
                //     $credential->setValueOptions(
                //         [-1 => 'fuck???', 1 => 'yes', 0 => 'no']
                //     );
                //     $credential->setValue($value === true ? "1" : "0");
                // } else {
                //     $credential->setValue("-1")
                //         ->setAttribute("disabled", "disabled");
                //     //echo "we set cert to -1 at ".__LINE__ . "<br>";
                // }
            } else {
                // form was populated with POST, not objects
                $language_id = $language;
                $key = array_search($language_id, array_column($language_options, 'value'));

                $label = $language_options[$key]['label'];
                // echo "\$key is $key, label $label ...<br>";
                $certifiable = $language_options[$key]['attributes']['data-certifiable'];
                //echo " and \$certifiable is: $certifiable ....";
                //if ('Spanish'==$label) {var_dump($credential->getValue());}
                // if (! $certifiable) {
                //     $credential->setValue("-1")
                //             ->setAttribute("disabled", "disabled");
                //     //echo "we set cert to -1 for $label at ".__LINE__ . "<br>";
                // } else {
                //     $credential->setValueOptions(
                //         [-1 => '', 1 => 'yes', 0 => 'no']
                //     );
                // }
            }

            $hidden_element->setValue($language_id);
            $language_markup = $this->view->formElement($hidden_element);
            $language_markup .= $label;
            $credential->setAttribute('id', "language-certification-$language_id");
            // if (! $certifiable) {
            //     //echo "adding hidden for $label...";
            //     //$credential->setValue('-1')->setAttribute ("disabled","disabled");
            //     $credential_markup = $this->view->formElement($credential);
            //     $credential_markup .= sprintf(
            //         '<input type="hidden" name="interpreter[interpreterLanguages]'
            //         . '[%d][federalCertification]" value="-1">',
            //         $index
            //     );
            //} else {
                $credential_markup = $this->view->formElement($credential);
            //}
            // printf("iteration %d: now it's dark at %d<br>", $index, __LINE__);
            $messages = $collection->getMessages();
            if ($messages){  // && $certifiable && -1 == $credential->getValue()) {
                $error_message = array_values($messages)[0];
                $errors = sprintf($this->error_template, 'block', $error_message);
            } else {
                $errors = sprintf($this->error_template, 'none', '');
            }
            $html .= sprintf($this->template, $language_id, $language_markup,
            $language_id, $credential_markup, $errors);
        }

        return $html;
    }

    /**
     * renders interpreter-language elements using input array
     *
     * @param array $params
     * @return string
     */
    public function fromArray(array $params)
    {
        $language = $params['language'];
        $i = $params['index'];
        $label = $this->view->escapeHtml($language->getName());
        $language_id = $language->getId();
        $language_markup = sprintf(
            '<input type="hidden" name="interpreter[interpreterLanguages]'
                       . '[%d][language]" value="%d">',
            $i,
            $language_id
        );
        $language_markup .= $label;

        $credential_element = new \Zend\Form\Element\Select(
            "interpreter[interpreterLanguages][$i][languageCredential]",
            ['value_options' => [''=>' ']+$params['credential_options'],
                    'attributes' => [
                        'class' => 'form-control',
                        'id'    => "fed-certification-$language_id",
                    ]
            ]
        );
        $credential_element->setAttributes(['class' => 'form-control']);
        $credential_markup = $this->view->formSelect($credential_element);

        // if (! $language->isFederallyCertified()) {
        //     // disable element, append a hidden
        //     $credential_element->setAttribute('disabled', 'disabled');
        //     $name = "interpreter[interpreterLanguages][$i][federalCertification]";
        //     $credential_markup .= sprintf(
        //         '<input type="hidden" name="%s" value="-1">',
        //         $name
        //     );
        // } else {
        //     $credential_element
        //             ->setValueOptions([-1 => '',1 => 'yes', 0 => 'no'])->setValue("-1");
        //     $credential_markup = $this->view->formSelect($credential_element);
        // }
        $errors = sprintf($this->error_template, 'none', '');
        return sprintf(
            $this->template,
            $language_id,
            $language_markup,
            $language_id,
            $credential_markup,
            $errors
        );
    }
}
