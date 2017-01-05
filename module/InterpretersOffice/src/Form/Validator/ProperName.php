<?php

/** module/InterpretersOffice/src/Form/Validator/ProperName.php */

namespace InterpretersOffice\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * class for validating proper names.
 */
class ProperName extends AbstractValidator
{
    /** @var string */
    const INVALID_LASTNAME = 'invalidLastname';
    /** @var string */
    const INVALID_MIDDLENAME = 'invalidMiddlename';
    /** @var string */
    const INVALID_FIRSTNAME = 'invalidFirstname';
    /** @var string */
    const FAKE_PLACEHOLDER_NAME = 'fakePlaceholderName';

    /**
     * regular expressions for validating names.
     *
     * @var array
     */
    protected $patterns = [

        'last' => "/[^\pL '-]/u",
        'first' => '/^(\p{Lu}\.( \pL+)?|[\pL ]+)$/u',
        'middle' => '/^(\pL\.?|\pL+)( (\pL\.|\pL+))*$/u',
    ];

    /**
     * error message templates.
     *
     * @var array
     */
    protected $messageTemplates = [

        self::INVALID_LASTNAME => 'last name contains invalid characters',
        self::INVALID_MIDDLENAME => 'middle name contains invalid characters',
        self::INVALID_FIRSTNAME => 'first name contains invalid characters',
        self::FAKE_PLACEHOLDER_NAME => "'%value%' does not appear to be a proper name",
    ];

    /**
     * the type of name: first, middle, or last.
     *
     * @var string
     */
    protected $type;

    /**
     * constructor.
     *
     * @param $options
     * required key: type => first|middle|last
     * optional key pattern => regular_expression to use
     */
    public function __construct(array $options)
    {
        parent::__construct();
        if (!isset($options['type'])) {
            throw new \Exception('parameter "type" is required (first, middle, or last)');
        }
        if (!in_array($options['type'], array_keys($this->patterns))) {
            throw new \Exception("type {$options['type']} is invalid, must be either 'first', 'middle', or 'last'");
        }
        $this->type = $options['type'];
        if (isset($options['pattern'])) {
            $this->patterns[$this->type] = $options['pattern'];
        }
    }

    /**
     * validates the name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isValid($name)
    {
        $this->setValue($name);
        if (in_array(strtolower($name), ['fnu', 'lnu', 'unknown'])) {
            $this->error(self::FAKE_PLACEHOLDER_NAME, $name);

            return false;
        }
        if ($this->type == 'middle') {
            return $this->isValidMiddleName($name);
        }

        if ('first' == $this->type) {
            return $this->isValidFirstname($name);
        }
        // by elimination, it must be a last name

        // this one uses a blacklist because we could
        // not figure out how to do it otherwise
        if (preg_match($this->patterns['last'], $name)) {
            // one more check: suffix Jr. or Sr.
            if (preg_match('/(.+)(,? )(J|S)r\.?$/U', $name, $matches)) {
                $surname = $matches[1];
                if (!preg_match($this->patterns['last'], $surname)) {
                    return true;
                }
            }
            $this->error(self::INVALID_LASTNAME);

            return false;
        }

        return true;
    }
    /**
     * validates a middle name.
     *
     * @param string $name
     *
     * @return bool true if valid
     */
    public function isValidMiddleName($name)
    {
        $pattern = $this->patterns['middle'];
        if (!preg_match($pattern, $name)) {
            $this->error(self::INVALID_MIDDLENAME);

            return false;
        }

        return true;
    }
    /**
     * validates a first name.
     *
     * @param string $name
     *
     * @return bool true if valid
     */
    public function isValidFirstname($name)
    {
        $pattern = $this->patterns['first'];

        if (!preg_match($pattern, $name)) {
            $this->error(self::INVALID_FIRSTNAME);

            return false;
        }

        return true;
    }
}
