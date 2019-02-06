<?php
/** module/InterpretersOffice/src/Service/DateCalculatorTrait.php*/

namespace InterpretersOffice\Service;

use InterpretersOffice\Entity\CourtClosing;

/**
 * for convenience
 *
 */
trait DateCalculatorTrait
{

    /**
     * DateCalculator
     *
     * @var DateCalculator
     */
    protected $dateCalc;

    /**
     * gets DateCalculator
     *
     * @return DateCalculator
     */
    public function getDateCalc()
    {
        if ($this->dateCalc) {
            return $this->dateCalc;
        }
        $repo = $this->objectManager->getRepository(CourtClosing::class);
        $this->dateCalc = new DateCalculator($repo);

        return $this->dateCalc;
    }

    /**
     * gets datetime two business days from $date.
     *
     * @param  \DateTime $date
     * @return string
     */
    public function getTwoBusinessDaysAfterDate(\DateTime $date)
    {
        return $this->getDateCalc()->getTwoBusinessDaysAfter($date);
    }

    public function getTwoBusinessDaysBefore(\Datetime $date)
    {
        return $this->getDateCalc()->getTwoBusinessDaysBefore($date);
    }

}
