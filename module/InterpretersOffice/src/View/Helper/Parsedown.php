<?php /** module/InterpretersOffice/src/View/Helper/Parsedown.php */
namespace InterpretersOffice\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Parsedown as Parser;

/**
 * viewhelper for rendering markdown
 */
class Parsedown extends AbstractHelper
{
    /**
     * parser
     *
     * @var Parser
     */
    private $parsedown;

    /**
     * gets parser
     *
     * @return Parses
     */
    private function getParser()
    {
        if (! $this->parsedown) {
            $this->parsedown = new Parser();
        }

        return $this->parsedown;
    }

    /**
     * __invoke
     *
     * @param string $string
     * @return string
     */
    public function __invoke(string $string)
    {
        return $this->getParser()->text($string);
    }
}
