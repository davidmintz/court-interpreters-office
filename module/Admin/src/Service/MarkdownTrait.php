<?php /** module/Admin/src/Service/MarkdownTrait.php */

declare(strict_types=1);
namespace InterpretersOffice\Admin\Service;

use Parsedown;
use Laminas\Filter;
use Laminas\Filter\HtmlEntities;

/**
 * for handling Markdown
 */
trait MarkdownTrait
{
    /**
     * Parsedown instance
     *
     * @var Parsedown
     */
    private $parseDown;

    /**
     * html entity filter
     * @var Filter\HtmlEntities
     */
    private $htmlentity_filter;

    /**
     * renders markdown as HTML
     *
     * @param  string $content
     * @return string
     */
    public function parsedown(string $content) : string
    {
        if (! $this->parseDown) {
            $this->parseDown = new Parsedown();
        }

        return $this->parseDown->text($content);
    }

    /**
     * escapes $content
     *
     * @return string
     */
    public function escape(string $content) : string
    {
        if (! $this->htmlentity_filter) {
            $this->htmlentity_filter = new Filter\HtmlEntities();
        }

        return $this->htmlentity_filter->filter($content);
    }
}
