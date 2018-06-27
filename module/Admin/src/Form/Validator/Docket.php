<?php
/**
 * module/Admin/src/Form/Validator/Docket.php
 */

namespace InterpretersOffice\Admin\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * validates the docket number
 *
 * @author david
 */
class Docket extends AbstractValidator
{
    const REGEX = '/^(19|20)\d\d-(C(IV|R)|MAG)-\d{4,5}$/';
    const INVALID_DOCKET = 'invalidDocket';
    const RIGHT_PART_CANNOT_BE_ZERO = 'rightPartCannotBeZero';

    /**
     * message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_DOCKET => 'invalid docket number',
        self::RIGHT_PART_CANNOT_BE_ZERO => 'right part of docket number cannot be zero',
    ];

    /**
     * implements ValidatorInterface
     *
     * @param string $docket
     * @return boolean true if valid
     */
    public function isValid($docket)
    {

        if (! preg_match(self::REGEX, $docket)) {
            $this->error(self::INVALID_DOCKET);
            return false;
        }
        if (! preg_match('/[1-9]/', substr($docket, -5))) {
            $this->error(self::RIGHT_PART_CANNOT_BE_ZERO);
            return false;
        }
        return true;
    }
}
