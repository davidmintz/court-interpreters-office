<?php
/** module/InterpretersOffice/src/Form/Validator/NoObjectExists.php */
namespace InterpretersOffice\Form\Validator;
use DoctrineModule\Validator\NoObjectExists as DoctrineModuleNoObjectExists;


/**
 * workaround for the problem where the Doctrine validator will not handle
 * multiple fields.
 * 
 * @see https://github.com/doctrine/DoctrineModule/issues/252
 * borrowed from:
 * @see https://kuldeep15.wordpress.com/2015/04/08/composite-key-type-duplicate-key-check-with-zf2-doctrine/
 */
class NoObjectExists extends DoctrineModuleNoObjectExists
{

    /**
     * {@inheritDoc}
     */
    public function isValid($value, $context = null)
    {
        foreach($this->fields as $name => $val)
        {
            $valueArray[] = $context[$val];
        }    
        $value = $this->cleanSearchValue($valueArray);
        
        $match = $this->objectRepository->findOneBy($value);
        
        if (is_object($match)) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);

            return false;
        }

        return true;
    }
}
