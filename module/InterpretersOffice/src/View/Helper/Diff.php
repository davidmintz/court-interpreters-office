<?php
/** module/InterpretersOffice/src/View/Helper/Diff.php */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper for detailed, human-friendly view of Event and Request entities,
 * highlighting what was updated by the user.
 *
 * Both the state before and after have to be assigned to the ViewModel.
 *
 */
class Diff extends AbstractHelper
{

    /**
     * current state of the entity
     *
     * @var array
     */
    protected $data;

    /**
     * state of the entity before last update
     *
     * @var array
     */
    protected $before;

    /**
     * gets our entity data
     *
     * @return array
     */
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }
        $this->data = $this->getView()->entity;

        return $this->data;
    }

    /**
     * gets our entity's previous state
     *
     * @return array
     */
    public function getPrevious()
    {
        if ($this->before) {
            return $this->before;
        }
        $this->before = $this->getView()->before;

        return $this->before;
    }

    /**
     * Highlights the difference between before and after update.
     *
     * In a viewscript, this is invoked as <code>$this->diff('field_name')</code>
     *
     * @param  string $field  the field whose value we're displaying
     * @return string the value(s), decorated with <INS> and <DEL> tags to
     * show what has changed.
     *
     * @todo consider using this helper ONLY if we know there's a previous state
     * for comparison.
     */
    public function __invoke($field)
    {
        $before = $this->getPrevious();
        $data = $this->getData()[$field];
        // if there's no update, render as per normal, getting it done
        // as soon as possible
        if ($before[$field] == $data) {
            if (is_string($data)) {
                return $this->getView()->escapeHtml($data);
            } elseif (is_array($data)) {
                return $field == 'interpreters' ? $this->renderInterpreters($data)
                        : $this->renderDefendants($data);
            } elseif ($data instanceof \DateTime) {
                return $this->renderDateTime($field, $data);
            }
        }
        // yes, there is a diff to show them
        if (false !== strstr($field, 'comments') && $this->getView()->with_comments) {
            return $this->htmlDiff($before[$field], $data);
        }
        if ($field == 'location') {
            $is_default = ! empty($this->getData()['is_default_location']);
            if ($is_default && !$before['location'] ) {
                return $data;
            }
        }
        if (is_string($data) || is_string($before[$field])) {
            if (! $data or ! $before[$field]) {
                $sep = '';
            } else {
                $sep = ' ';
            }
            return sprintf('<del>%s</del>%s<ins>%s</ins>', $before[$field], $sep, $data);
        }

        if ($data instanceof \DateTime) {
            $string_before = $this->renderDateTime($field, $before[$field]);
            $string_after  = $this->renderDateTime($field, $data);
            if ($string_before != $string_after) {
                return sprintf(
                    '<del class="avoidwrap">%s</del> <ins class="avoidwrap">%s</ins>',
                    $string_before,
                    $string_after
                );
            } else {
                return $string_after;
            }
        }
        if (is_array($data)) {
            $interpreters = $field == 'interpreters';
            if ($interpreters) {
                $indexed_after = array_combine(array_column($data, 'id'), $data);
                $indexed_before = array_combine(array_column($before[$field], 'id'), $before[$field]);
                $added = array_diff(array_keys($indexed_after), array_keys($indexed_before));
                $deleted = array_diff(array_keys($indexed_before), array_keys($indexed_after));
                $all_ids = array_unique(array_merge(array_keys($indexed_after), array_keys($indexed_before)));
                $return = '';
                $format = '<span class="interpreter" data-id="%s" data-email="%s">%s</span><br>';
                foreach ($all_ids as $id) {
                    if (in_array($id, $deleted)) {
                        $i = $indexed_before[$id];
                        $return .= sprintf($format, $i['id'], $i['email'], '<del>'."$i[lastname], $i[firstname]".'</del>');
                    } elseif (in_array($id, $added)) {
                        $i = $indexed_after[$id];
                        $return .= sprintf($format, $i['id'], $i['email'], '<ins>'."$i[lastname], $i[firstname]".'</ins>');
                    } else {
                        $i = $indexed_after[$id];
                        $return .= sprintf($format, $i['id'], $i['email'], "$i[lastname], $i[firstname]");
                    }
                }

                return $return;
            }
            // else, it's defendant names
            $flatten = function ($n) {
                return "$n[surnames], $n[given_names]";
            };
            $names_now = array_map($flatten, $data);
            $names_before = array_map($flatten, $before[$field]);
            $new = array_diff($names_now, $names_before);
            $deleted = array_diff($names_before, $names_now);
            $all = array_unique(array_merge($names_now, $names_before));
            $return = '';
            foreach ($all as $n) {
                if (in_array($n, $deleted)) {
                    $return .= "<del>$n</del><br>";
                } elseif (in_array($n, $new)) {
                    $return .= "<ins>$n</ins><br>";
                } else {
                    $return .= "$n<br>";
                }
            }

            return $return;
        }
    }


    /**
     * renders defendant names
     *
     * @param  Array  $data
     * @return string
     */
    public function renderDefendants(Array $data)
    {

        return implode('<br>', array_map(function ($d) {
            return  "$d[surnames], $d[given_names]";
        }, $data));
    }

    /**
     * renders interpreter names
     *
     * @param  Array  $data
     * @return string
     */
    public function renderInterpreters(Array $data)
    {

        return implode('<br>', array_map(function ($i) {
            return  sprintf(
                '<span class="interpreter" data-id="%s" data-email="%s">%s</span>',
                $i['id'],
                $i['email'],
                "{$i['lastname']}, {$i['firstname']}"
            );
        }, $data));
    }

    /**
     * renders PHP DateTime objects
     *
     * @param  string   $field name of field (property) holding the DateTime
     * @param  \DateTime $obj
     * @return string
     */
    public function renderDateTime($field, \DateTime $obj)
    {

        switch ($field) {
            case 'time':
            case 'end_time':
            case 'submission_time':
                $format = 'g:i a';
                break;
            case 'date':
            case 'submission_date':
                $format = 'd-M-Y';
                break;
            case 'modified':
            case 'last_updated':
            case 'created':
                $format = 'd-M-Y g:i a';
                break;
            default:
                $format = 'r';
        }
        return $obj->format($format);
    }


    /*  lifted from from https://github.com/paulgb/simplediff:

        Paul's Simple Diff Algorithm v 0.1
        (C) Paul Butler 2007 <http://www.paulbutler.org/>
        May be used and distributed under the zlib/libpng license.

        This code is intended for learning purposes; it was written with short
        code taking priority over performance. It could be used in a practical
        application, but there are a few ways it could be optimized.

        Given two arrays, the function diff will return an array of the changes.
        I won't describe the format of the array, but it will be obvious
        if you use print_r() on the result of a diff on some test data.

        htmlDiff is a wrapper for the diff command, it takes two strings and
        returns the differences in HTML. The tags used are <ins> and <del>,
        which can easily be styled with CSS.

        ----
        thanks, Paul!
    */
    /**
     * computes difference between strings
     *
     * @param  string $old
     * @param  string $new
     * @return string
     */
    public function diff($old, $new)
    {
        $matrix = [];
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) {
            return [['d' => $old, 'i' => $new]];
        }
        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    /**
     * renders difference between strings as HTML
     *
     * @param  string $old
     * @param  string $new
     * @return string
     */
    public function htmlDiff($old, $new)
    {
        $ret = '';
        $view = $this->getView();
        $diff = $this->diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= (! empty($k['d']) ? "<del>".$view->escapeHtml(implode(' ', $k['d']))."</del> " : '').
                    (! empty($k['i']) ? "<ins>".$view->escapeHtml(implode(' ', $k['i']))."</ins> " : '');
            } else {
                $ret .= $view->escapeHtml($k) . ' ';
            }
        }
        return $ret;
    }
}
