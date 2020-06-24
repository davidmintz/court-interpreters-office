<?php
/**
 * module/Admin/src/Form/Validate/EventSubmissionDateTime.php
 */

namespace InterpretersOffice\Admin\Form\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * validates event submission date/time
 */
class EventSubmissionDateTime extends AbstractValidator
{

    const EVENT_PRECEDES_SUBMISSION = 'eventPreceedsSubmission';

    const EVENT_PRECEDES_SUBMISSION_BY_TOO_MUCH =
            'eventPreceedsSubmissionByTooMuch';

    const FUTURE_SUBMISSION_DATE_TIME = 'futureSubmissionDateTime';

    const INVALID_SUBMISSION_DATETIME = 'invalidSubmissionDateTime';

    /**
     * max negative minutes between event and submission datetimes
     *
     * The default value of 60 minutes means that users can record a request
     * for services as having been submitted up to 60 minutes _after_
     * its scheduled time. Consumers of interpreting services actually do
     * sometimes call, e.g., at 11:07 to request an interpreter for an event
     * scheduled at 11:00 the same day.
     *
     * @var int
     */
    protected $max_negative_minutes = 60;

    /**
     * message variable for max negative minutes
     *
     * @var int
     */
    public $max_minutes = 60;

    /**
     * message variables
     *
     * @var array
     */
    protected $messageVariables = [
        'max' => 'max_minutes',
    ];

    /**
     * error message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        self::EVENT_PRECEDES_SUBMISSION =>
            'submission date and time cannot be after the event',
        self::EVENT_PRECEDES_SUBMISSION_BY_TOO_MUCH =>
            'submission date and time cannot be more than %max% minutes '
            . 'after the event',
        self::FUTURE_SUBMISSION_DATE_TIME =>
        'submission date and time cannot be in the future',
        self::INVALID_SUBMISSION_DATETIME => 
            'submission date/time format is invalid',
    ];

    /**
     * constructor
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        if (isset($options['max_negative_minutes'])) {
            $this->max_negative_minutes = $options['max_negative_minutes'];
        }
    }

    /**
     * implements ValidatorInterface
     *
     * Other validators on these fields must precede this one in the chain and
     * have their set break_chain_on_failure set to true, or else  bad things
     * might happen.
     *
     * @param  mixed $value
     * @param array $context
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value, $context = null)
    {
        if (! isset($context['date'])) { // multi-date mode
            if (isset($context['dates']) && is_array($context['dates'])) {
                $dates = $context['dates'];
                sort($dates);
                $context['date'] = $dates[0];
            } else {
                // hmmm
                return false;
            }
        }
        try {
            $event_datetime = new \DateTime($context['date'].' '.$context['time']);
            $submission_datetime = new \DateTime($value .' '.$context['submission_date']);
        } catch (\Exception $e) {
            $this->error(self::INVALID_SUBMISSION_DATETIME);
            return false;
        }
        $now = new \DateTime();
        if ($submission_datetime > $now) {
            $this->error(self::FUTURE_SUBMISSION_DATE_TIME);
            return false;
        }
        $diff = $submission_datetime->diff($event_datetime);
        if (0 == $diff->invert) {
            // any non-negative value is good
            return true;
        }

        if (! $this->max_negative_minutes) {
            // we're not allowing negative notice
            $this->error(self::EVENT_PRECEDES_SUBMISSION);
            return false;
        }
        // compare our diff in minutes to the allowed interval
        $minutes_diff = abs(($event_datetime->getTimestamp() -
                $submission_datetime->getTimestamp()) / 60);
        if ($context['time'] && $minutes_diff > $this->max_negative_minutes) {
            $this->error(self::EVENT_PRECEDES_SUBMISSION_BY_TOO_MUCH);
            return false;
        }

        return true;
    }
}
