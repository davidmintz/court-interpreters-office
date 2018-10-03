<?php
namespace InterpretersOffice\Requests\Form\Validator;

use Zend\Validator\AbstractValidator;
use InterpretersOffice\Entity\Repository\CourtClosingRepository;

class RequestDateTime extends AbstractValidator
{

const LESS_THAN_TWO_BUSINESS_DAYS_NOTICE = 'lessThanTwoBusinessDaysNotice';
const DATE_IS_IN_THE_PAST = 'dateIsInThePast';
const DATE_IS_NOT_A_BUSINESS_DAY = 'dateIsNotABusinessDay';

	/** @var CourtClosingRepository */
	protected $repository;

	protected $messageTemplates = [
            self::LESS_THAN_TWO_BUSINESS_DAYS_NOTICE =>
		'A minimum two full business days\' notice is required. For assistance in emergent matters please contact the Interpreters by phone.',

            self::DATE_IS_IN_THE_PAST => 'Invalid date. Request date has to be in the future.',

            self::DATE_IS_NOT_A_BUSINESS_DAY => 'Date is not a business day',
	];

	public function __construct(Array $options)
	{

		if (! isset($options['repository'])) {
			throw new \Exception(sprintf('%s constructor requires "repository" option',__CLASS__));
		}
		if (! $options['repository'] instanceof CourtClosingRepository) {
			throw new \Exception('option "repository" must be instance of InterpretersOffice\Entity\CourtClosingRepository');
		}

		$this->repository = $options['repository'];
		parent::__construct($options);

	}

	public function isValid($date,$context=null) {

		if (! isset($context['time']) or !$context['time']) {
			return true; // and let other validators handle it
		}
		try {
			$request_datetime = new \DateTime("$date $context[time]");
		} catch (\Exception $e) {
			// time is malformed; let another validator handle it
			return true;
		}

        // make sure it is not a weekend or holiday
        /* something like...
         *
           $q = $em->createQuery('SELECT date FROM Application\Entity\CourtClosing date WHERE date.date = :date');
           $q->setParameters([':date'=> new \DateTime('2016-07-04')])->getResult();
           if (count($result)) { } // it's a holiday
         */

		$diff = $this->repository->getDateDiff($request_datetime);
		if ($diff->invert) {
            $this->error(self::DATE_IS_IN_THE_PAST);
            return false;
        }
		if ($diff->days < 2) {
            $this->error(self::LESS_THAN_TWO_BUSINESS_DAYS_NOTICE);
            return false;
		}

		return true;
	}

}
