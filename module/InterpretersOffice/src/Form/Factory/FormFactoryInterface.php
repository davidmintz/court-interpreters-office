<?php
/**
 * /opt/www/court-interpreters-office/module/InterpretersOffice/src/Form/Factory/FormFactoryInterface.php.
 */

namespace InterpretersOffice\Form\Factory;

/**
 * interface definition.
 */
interface FormFactoryInterface
{
    /**
     * creates a Laminas\Form\Form instance.
     *
     * @param object|string $entityObjectOrClassname entity instance or classname
     * @param array         $options
     */
    public function createForm($entityObjectOrClassname, array $options);
}
