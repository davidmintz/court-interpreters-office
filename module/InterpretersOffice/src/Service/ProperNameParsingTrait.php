<?php
/**
 * module/InterpretersOffice/src/Service/ProperNameParsingTrait.php
 */
namespace InterpretersOffice\Service;

/**
 * convenience trait for re-use elsewhere in the application
 */
trait ProperNameParsingTrait {
    
    
    /**
     * parses first and last names out of $input. 
     * 
     * expected format is la[stname][,f[irstname]]. returns an 
     * array with keys 'last' and 'first'
     * 
     * @param string $input
     * @return array
     */
    public function parseName($input)
    {
        
        $name = preg_split('/ *, */',trim($input),2,PREG_SPLIT_NO_EMPTY);
        if (2 == sizeof($name)) {
            list($last, $first) = $name;
        } else {
            $last = $name[0];
            $first = false;
        }
        return compact('last','first');
    }    
}
