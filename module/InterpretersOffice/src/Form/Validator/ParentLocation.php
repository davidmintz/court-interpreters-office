<?php

/** module/InterpretersOffice/src/Form/Validator/LocationType.php */

namespace InterpretersOffice\Form\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * Description of LocationType.
 *
 * @author david
 */
class ParentLocation extends AbstractValidator
{
    /** @var string */
    const LOCATION_TYPE_CANNOT_HAVE_PARENT = 'locationTypeCannotHaveParent';

    /** @var string */
    const LOCATION_TYPE_MUST_HAVE_PARENT = 'locationTypeMustHaveParent';

    /** @var string */
    const INVALID_PARENT_TYPE = 'invalidParentType';

    /**
     * array of location (array )entities that have no parent.
     *
     * @var array
     */
    protected $parentLocations;

    /**
     * array of locationType (array) entities.
     *
     * @var array
     */
    protected $locationTypes;

    /**
     * constructor.
     *
     * @param array|Traversable $options
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function __construct($options)
    {
        if (! key_exists('parentLocations', $options)) {
            throw new \Exception('missing required parentLocation option');
        }
        if (! key_exists('locationTypes', $options)) {
            throw new \Exception('missing required locationTypes option');
        }
        if (! is_array($options['parentLocations'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'parentLocations should be array, got %s',
                    gettype($options['parentLocations'])
                )
            );
        }
        if (! is_array($options['locationTypes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'locationTypes should be an array, got %s',
                    gettype($options['locationTypes'])
                )
            );
        }

        $this->parentLocations = array_column($options['parentLocations'], null, 'value');
        $this->locationTypes = array_column($options['locationTypes'], 'label', 'value');
        parent::__construct($options);
    }

    /**
     * error message templates.
     *
     * @var array
     */
    protected $messageTemplates = [

        self::LOCATION_TYPE_CANNOT_HAVE_PARENT => 'this type of location cannot have any parent location',
        self::LOCATION_TYPE_MUST_HAVE_PARENT => 'this type of location has to have a parent location',
        self::INVALID_PARENT_TYPE => 'this type of location is incompatible with this parent location\'s type (%value%)',

    ];
    /**
     * Tests whether the type of location chosen is compatible with the parent
     * location type, if any.
     *
     * This assumes an underlying location_types database table populated with
     * certain values, e.g., courthouse, courtroom, etc.
     *
     * @param int   $value
     * @param array $context
     *
     * @return bool true if valid
     */
    public function isValid($value, $context = null)
    {
        if (! key_exists($context['parentLocation'], $this->parentLocations)) {
            $type_of_parent = null;
        } else {
            $type_of_parent = key_exists('attributes', $this->parentLocations[$context['parentLocation']]) ?
                $this->parentLocations[$context['parentLocation']]['attributes']['data-location-type'] : null;
        }

        $type_submitted = $this->locationTypes[$value];

        if (in_array($type_submitted, ['courtroom', 'interpreters office', 'holding cell'])
                && ! $type_of_parent) {
            $this->error(self::LOCATION_TYPE_MUST_HAVE_PARENT);

            return false;
        }

        if ('courtroom' == $type_submitted && ! in_array(
            $type_of_parent,
            ['jail', 'courthouse']
        )) {
            $this->error(self::INVALID_PARENT_TYPE, $type_of_parent);

            return false;
        }

        if (in_array($type_submitted, ['jail', 'courthouse']) && $type_of_parent) {
            $this->error(self::LOCATION_TYPE_CANNOT_HAVE_PARENT);

            return false;
        }
        /* @todo  new rule:  courthouse can't be in a courthouse */
        return true;
    }
}
