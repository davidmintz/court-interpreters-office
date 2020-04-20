<?php
/** module/InterpretersOffice/src/Form/Validator/PasswordStrength.php  */

namespace InterpretersOffice\Form\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * enforces password strength policy
 * 
 * frankly, I would prefer https://pypi.org/project/password-strength/ but this
 * will have to do for the time being
 */
class PasswordStrength extends AbstractValidator
{

    /** @var string */
    const TOO_WEAK = 'tooWeak';

     /**
     * error message templates.
     *
     * @var array
     */
    protected $messageTemplates = [

        self::TOO_WEAK => 'password is too weak',

    ];
    
    /**
     * validation
     * 
     * @param string $value
     * @param array $context
     */
    public function isValid($value, $context = null) : bool
    {
        $upper =  preg_match('/[A-Z]/',$value);
        $lower =   preg_match('/[a-z]/',$value);
        $digit = preg_match('/[0-9]/',$value);
        if (! ($upper && $lower && $digit)) {
            $this->error(self::TOO_WEAK);
            return false;
        }
        return true;
    }
}