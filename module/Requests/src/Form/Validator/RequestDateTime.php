<?php
/** module/Requests/src/Form/Validator/RequestDateTime.php */

namespace InterpretersOffice\Requests\Form\Validator;

use Zend\Validator\AbstractValidator;
use InterpretersOffice\Entity\Repository\CourtClosingRepository;
use InterpretersOffice\Service\DateCalculator;

/**
 * validator to enforce two-business-day deadline
 */
class RequestDateTime extends AbstractValidator
{

    const LESS_THAN_TWO_BUSINESS_DAYS_NOTICE = 'lessThanTwoBusinessDaysNotice';
    const DATE_IS_IN_THE_PAST = 'dateIsInThePast';
    const DATE_IS_NOT_A_BUSINESS_DAY = 'dateIsNotABusinessDay';
    const DATETIME_INVALID = 'datetimeInvalid';


    /**
     * CourtClosing repository.
     *
     * @var CourtClosingRepository
     */
    protected $repository;

    /**
     * message templates.
     *
     * @var array
     */
    protected $messageTemplates = [
            self::LESS_THAN_TWO_BUSINESS_DAYS_NOTICE =>
        'A minimum two full business days\' notice is required. For assistance in emergent matters please contact the Interpreters by phone.',

            self::DATE_IS_IN_THE_PAST => 'Invalid date. Request date has to be in the future.',
            self::DATETIME_INVALID => 'Invalid date/time format',
            // not yet in use
            self::DATE_IS_NOT_A_BUSINESS_DAY => 'Date is not a business day',

    ];

    /**
     * constructor.
     *
     * @param Array $options
     */
    public function __construct(Array $options)
    {

        if (! isset($options['repository'])) {
            throw new \Exception(sprintf('%s constructor requires "repository" option', __CLASS__));
        }
        if (! $options['repository'] instanceof CourtClosingRepository) {
            throw new \Exception('option "repository" must be instance of InterpretersOffice\Entity\CourtClosingRepository');
        }

        $this->repository = $options['repository'];
        parent::__construct($options);
    }

    /**
     * is it valid?
     *
     * @param  string  $date
     * @param  array  $context
     * @todo reconsider returning true when it's not really valid
     * @return boolean
     */
    public function isValid($date, $context = null)
    {

        if (! isset($context['time']) or ! $context['time']) {
            return true; // and let other validators handle it
        }
        try {
            $request_datetime = new \DateTime("$date $context[time]");
        } catch (\Exception $e) {
            $this->error(self::DATETIME_INVALID);
            return false;
        }
        $now = new \DateTime();
        if ($request_datetime < $now) {
            $this->error(self::DATE_IS_IN_THE_PAST);
            return false;
        }
        $calc = new \InterpretersOffice\Service\DateCalculator($this->repository);
        $deadline = $calc->getTwoBusinessDaysAfter($now);
        if ($request_datetime < $deadline) {
            $this->error(self::LESS_THAN_TWO_BUSINESS_DAYS_NOTICE);
            return false;
        }
        // make sure it is not a weekend or holiday?
        /* something like...
         *
           $q = $em->createQuery('SELECT date FROM Application\Entity\CourtClosing date WHERE date.date = :date');
           $q->setParameters([':date'=> new \DateTime('2016-07-04')])->getResult();
           if (count($result)) { } // it's a holiday
         */

        // $diff = $this->repository->getDateDiff($request_datetime);
        // if ($diff->invert) {
        //     $this->error(self::DATE_IS_IN_THE_PAST);
        //     return false;
        // }
        // if ($diff->days < 2) {
        //     $this->error(self::LESS_THAN_TWO_BUSINESS_DAYS_NOTICE);
        //     return false;
        // }

        return true;
    }
}
