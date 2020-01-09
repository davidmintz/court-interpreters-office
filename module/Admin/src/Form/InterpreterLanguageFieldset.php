<?php
/**
 * module/InterpretersOffice/src/Form/InterpreterLanguageFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

/**
 * Fieldset for Interpreter's working languages.
 *
 */
class InterpreterLanguageFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct('interpreterLanguages', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\InterpreterLanguage());

        $this->add([
            'name' => 'language',
            'type' => 'hidden',
        ]);
        $credential_options = $objectManager->getRepository(Entity\Language::class)
            ->getCredentialOptions();
        $this->add([
            'name' => 'languageCredential',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'value_options' => ['' => ''] + $credential_options,
            ],
            'attributes' => [
                'class' => 'form-control'
            ]

        ]);
    }

    /**
     * implements InputFilterProviderInterface.
     * @todo rethink how to validate, without hard-coding rules based on the
     * federal court classification system.
     * @return array
     */
    public function getInputFilterSpecification()
    {
        //$repository = $this->getObjectManager()->getRepository(Entity\Language::class);
        //$certified_languages = $repository->findAllCertifiedLanguages();
        $spec = [
            'languageCredential' => [
                'required' => true,
                'allow_empty' => false,
                // no this does not appear to work
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'shit is required',
                            ],
                        ],
                    ],
                ],
            ]
        ];


        $spec['language'] = [
                'required' => true,
                'allow_empty' => false,
                // objectexists validator ?
        ];
        //print_r($spec);
        return $spec;
    }
}
