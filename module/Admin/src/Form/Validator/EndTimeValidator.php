<?php /** module/Admin/src/Form/Validator/EndTimeValidator.php */

namespace InterpretersOffice\Admin\Form\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * validates the event end time
 */
class EndTimeValidator extends AbstractValidator
{

    /**
     * message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        'invalid_format' => 'invalid time',
        'invalid_time' => 'end time has to be later than start time',
        'missing_start_time' =>
            'if end time is provided, start time is required',
        'is_future' => 'end time cannot be predicted for future events',
    ];

    /**
     * isValid() implementation
     *
     * @param  string  $value
     * @param  array $context other variables in the fieldset/form
     * @return boolean true if valid
     */
    public function isValid($value, $context = null)
    {
        if (! trim($value)) {
            return true;
        }
        if (isset($context['date']) && isset($context['time'])) {
            $datetime = strtotime("$context[date] $value");
            if ($datetime && time() < $datetime) {
                $this->error('is_future');
                return false;
            }
        }
        if ($value && ! $context['time']) {
            $this->error('missing_start_time');
            return false;
        }
        $end = strtotime($value);
        $start = strtotime($context['time']);
        if (false === $end) {
            $this->error('invalid_format');
            return false;
        }
        if ($start && $end && $end <= $start) {
            $this->error('invalid_time');
            return false;
        }
        return true;
    }
}
