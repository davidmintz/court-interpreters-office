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
<div class="offset-sm-1 col-sm-3  interpreter-language language-name pt-2" id="language-%d">
%s
</div>
<div class="col-sm-8 form-inline interpreter-language language-credential">
    <label for="language-credential-%d">credential:</label>
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
    protected $error_template =
        '<div class="col-sm-8 alert alert-warning validation-error credential-required" style="display:%s">%s</div>';

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
            // this is BULLSHIT!
            if (is_object($credential->getValue())) {
                $id = $credential->getValue()->getId();
                $credential->setValue($id);
            }
            if (is_object($language)) {
                // we were hydrated from an Interpreter entity
                $label = $this->view->escapeHtml($language->getName());
                $language_id = $language->getId();
                $certifiable = $language->isFederallyCertified();
            } else {
                // form was populated with POST, not objects
                $language_id = $language;
                $key = array_search($language_id, array_column($language_options, 'value'));
                $label = $language_options[$key]['label'];
                // echo "\$key is $key, label $label ...<br>";
                $certifiable = $language_options[$key]['attributes']['data-certifiable'];
            }
            $cred_options = $credential->getValueOptions();
            if (! $certifiable) {
                $i = array_search('AO', array_column($cred_options, 'label'));
                if (false !== $i) {
                    $cred_options[$i]['attributes'] = ['disabled' => 'disabled'];
                    $credential->setValueOptions($cred_options);
                }
            }
            $hidden_element->setValue($language_id);
            $language_markup = $this->view->formElement($hidden_element);
            $language_markup .= $label;
            $credential->setAttribute('id', "language-credential-$language_id");
            $credential_markup = $this->view->formElement($credential);
            $messages = $collection->getMessages();
            if ($messages) {
                $error_message = array_values($messages)[0];
                $errors = sprintf($this->error_template, 'block', $error_message);
            } else {
                $errors = sprintf($this->error_template, 'none', '');
            }
            $html .= sprintf(
                $this->template,
                $language_id,
                $language_markup,
                $language_id,
                $credential_markup,
                $errors
            );
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
        $certifiable = $language->isFederallyCertified();
        $cred_options = $params['credential_options'];
        if (! $certifiable) {
            $n = array_search('AO', array_column($cred_options, 'label'));
            if ($n !== false) {
                $cred_options[$n]['attributes'] = ['disabled' => 'disabled'];
            }
        }
        $credential_element = new \Zend\Form\Element\Select(
            "interpreter[interpreterLanguages][$i][languageCredential]",
            ['value_options' => ['' => ' '] + $cred_options,]
        );

        $credential_element->setAttributes(['id'    => "language-credential-$language_id",'class' => 'form-control']);
        $credential_markup = $this->view->formSelect($credential_element);
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
