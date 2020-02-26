<?php /** module/Admin/src/Service/DocketAnnotationService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;
use Doctrine\ORM\EntityManagerInterface;

use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use Laminas\Filter;

/**
 * docket-annotation management
 */
class DocketAnnotationService
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    private $filter;

    public function createInputFilter()
    {
        return (new InputFilter\Factory)->createInputFilter([
            'priority' => [
                'name' => 'priority',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'not_empty',
                        'options' => [],
                    ],
                ],
                'filters' => [

                ],
            ],
            'docket' => [
                'name' => 'docket',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'not_empty',
                        'options' => [],
                    ],
                    [

                    ]
                ],
                'filters' => [

                ],
            ],
            'comment' => [
                'name' => 'comment',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'not_empty',
                        'options' => [],
                    ],
                ],
                'filters' => [

                ],
            ],
        ]);
    }

    public function getInputFilter()
    {
        if (! $this->filter) {
            $this->filter = $this->createInputFilter();
        }
        return $this->filter;
    }
}
