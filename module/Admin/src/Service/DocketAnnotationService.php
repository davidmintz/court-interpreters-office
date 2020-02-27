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
use Laminas\Authentication\AuthenticationServiceInterface;
use Parsedown;


/**
 * docket-annotation management
 */
class DocketAnnotationService
{

    use MarkdownTrait;

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    private $auth;


    public function __construct(EntityManagerInterface $em,
        AuthenticationServiceInterface $auth)
    {
        $this->em = $em;
        $this->auth = $auth;
    }

    /**
     * input filter
     *
     * @var InputFilter
     */
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

    /**
     * gets input filter
     *
     * @return
     */
    public function getInputFilter()
    {
        if (! $this->filter) {
            $this->filter = $this->createInputFilter();
        }
        return $this->filter;
    }

    public function find(string $docket) : Array
    {
        $repo = $this->em->getRepository(Entity\DocketAnnotation::class);
        // we need to write our own repo and optimize this query
        return $repo->findByDocket($docket);
    }

    public function create(array $data)
    {

    }

    public function update(array $data, $id)
    {

    }

    public function delete()
    {

    }


}
