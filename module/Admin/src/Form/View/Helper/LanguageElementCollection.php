<?php
namespace InterpretersOffice\Admin\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class LanguageElementCollection extends AbstractHelper
{

	
	protected $template = <<<TEMPLATE

		shit

TEMPLATE;

	public function __invoke()
	{
		return $this->render();
	}

	public function render()
	{
		return "shit";
	}


}
