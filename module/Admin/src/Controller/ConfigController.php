<?php

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;

// use InterpretersOffice\Admin\Form\SearchForm;
// use InterpretersOffice\Entity;

/**
 * configuration controller
 */
class ConfigController extends AbstractActionController
{
    public function indexAction()
    {
        return [];
    }

}
