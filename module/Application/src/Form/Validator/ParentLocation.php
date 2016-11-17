<?php

/** module/Application/src/Form/Validator/LocationType.php */

namespace Application\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Description of LocationType
 *
 * @author david
 */
class ParentLocation extends AbstractValidator {
   
    /** @var string */
    const LOCATION_TYPE_CANNOT_HAVE_PARENT = 'locationTypeCannotHaveParent';
    
    /** @var string */
    const LOCATION_TYPE_MUST_HAVE_PARENT = 'locationTypeMustHaveParent';
    
    /** @var string */
    const INVALID_PARENT_TYPE  = 'invalidParentType';
    
    /** @var array */
    protected $parentLocations;
    
    /** @var array */
    protected $locationTypes;
    
    public function __construct($options) {
        
        if (! key_exists('parentLocations',$options)) {
            throw new \Exception('missing required parentLocation option');
        }
        if (! key_exists('locationTypes',$options)) {
            throw new \Exception('missing required locationTypes option');
        }
        if (! is_array($options['parentLocations'])) {
            throw new \InvalidArgumentException(
                sprintf('parentLocations should be array, got %s',
                        gettype($options['parentLocations']))
            );
        }
         if (! is_array($options['locationTypes'])) {
            throw new \InvalidArgumentException(
               sprintf('locationTypes should be an array, got %s',
                       gettype($options['locationTypes']))
             );
        }
        $this->parentLocations = array_column($options['parentLocations'],NULL,'value');
        $this->locationTypes = array_column($options['locationTypes'],'label','value');
        parent::__construct($options);
    }
    
    /**
     * error message templates.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::LOCATION_TYPE_CANNOT_HAVE_PARENT => 
            'this type of location cannot have any parent location',
        self::LOCATION_TYPE_MUST_HAVE_PARENT => 
            'this type of location has to have a parent location',
        self::INVALID_PARENT_TYPE => 
            'type of location selected is incompatible with this parent location\'s type',

    ];
    /**
     * Tests whether the type of location chosen is compatible with the parent 
     * location type, if any.
     * 
     * This assumes an underlying location_types database table populated with 
     * certain values, e.g., courthouse, courtroom, etc.
     * 
     * @param int $value
     * @param array $context
     * @return boolean true if valid
     */
    public function isValid($value, $context = null)
    {
        $type_of_parent = 
            key_exists('attributes',$this->parentLocations[$context['parentLocation']]) ? 
                $this->parentLocations[$context['parentLocation']]['attributes']['data-location-type'] : NULL;
        $type_submitted = $this->locationTypes[$value];
        if ( 'courtroom' == $type_submitted && ! $type_of_parent) {
            $this->error(self::LOCATION_TYPE_MUST_HAVE_PARENT);
            return false;
        }
        if ( 'courtroom' == $type_submitted && 'holding cell' == $type_of_parent) {
             $this->error(self::INVALID_PARENT_TYPE);
             return false;
        }
        if ('holding cell' == $type_submitted && ! $type_of_parent) {
            $this->error(self::LOCATION_TYPE_MUST_HAVE_PARENT);
            return false;
        } 
        if (in_array($type_submitted,['jail','courthouse']) && $type_of_parent) {
             $this->error(self::LOCATION_TYPE_CANNOT_HAVE_PARENT);
             return false;
        }
        
        
        
        //printf("<pre>%s</pre>", print_r($context,true));
        //printf("<pre>parent location type submitted: %s</pre>", print_r($this->parentLocations[$context['parentLocation']]['attributes']['data-location-type'],  true));
        //printf("<pre>location type submitted: %s</pre>", print_r($this->locationTypes[$value],  true));
        
        return true;
        
        
    }
}
