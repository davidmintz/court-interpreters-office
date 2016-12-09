<?php
/** module/InterpretersOffice/src/Form/Validator/UniqueObject.php */

namespace InterpretersOffice\Form\Validator;


use DoctrineModule\Validator\UniqueObject as DoctrineModuleUniqueObject;
/**
 * workaround for the problem that the Doctrine validator will not handle
 * multiple fields. 
 * 
 * @see https://github.com/doctrine/DoctrineModule/issues/252
 * closely based on 
 * @see https://kuldeep15.wordpress.com/2015/04/08/composite-key-type-duplicate-key-check-with-zf2-doctrine/
 */
class UniqueObject extends DoctrineModuleUniqueObject {
    
    /**
     * returns true if submitted value is not duplicated in the database
     * 
     * @param string $value
     * @param array $context
     * @return boolean
     */
    public function isValid($value,$context = null)
    {
        foreach($this->fields as $name => $val)
        {
            $valueArray[] = $context[$val];
        }    
        $value = $this->cleanSearchValue($valueArray);
        
        $match = $this->objectRepository->findOneBy($value);
        
        if (!is_object($match)) {
            return true;
        }

        $expectedIdentifiers = $this->getExpectedIdentifiers($context);
        $foundIdentifiers    = $this->getFoundIdentifiers($match);

        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) == 0) {
            return true;
        }

        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);
        return false;
    }
}
