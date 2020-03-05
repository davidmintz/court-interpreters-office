<?php
namespace InterpretersOffice\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Parsedown as Parser;

class Parsedown extends AbstractHelper
{
    /**
     * [private description]
     * @var Parser
     */
    private $parsedown;

    private function getParser()
    {
        if (!$this->parsedown) {
            $this->parsedown = new Parser();
        }

        return $this->parsedown;
    }

    public function __invoke(string $string)
    {
        return $this->getParser()->text($string);
    }
}
