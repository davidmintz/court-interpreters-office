<?php
/** module/InterpretersOffice/src/View/Helper/ErrorMessage.php */

namespace InterpretersOffice\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * view helper for error-message div
 */
class Diff extends AbstractHelper
{

    protected $data;
    protected $before;


    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }
        $this->data = $this->getView()->entity;
        return $this->data;
    }

    public function getPrevious()
    {
        if ($this->before) {
            return $this->before;
        }
        $this->before = $this->getView()->before;
        return $this->before;
    }
    public function __invoke($key)
    {
        $before = $this->getPrevious();
        $data = $this->getData()[$key];
        if (! $before or $before[$key] == $data) {
            // render as per normal
            if (is_string($data)) {
                return $data;
            } else {
                if (is_array($data)) {
                    return $key == 'interpreters' ? $this->renderInterpreters($data)
                        : $this->renderDefendants($data);
                } else {
                    if ($data instanceof \DateTime) {
                        return $this->renderDateTime($key,$data);
                    }
                }
            }
        }
        if (false !== strstr($key,'comments')) {
            return $this->htmlDiff($before[$key],$data);
        }
        // there is a diff
        if (is_string($data) || is_string($before[$key]) ) {//
            return sprintf('<del>%s</del> <ins>%s</ins>',$before[$key],$data);
        }
        if ($data instanceof \DateTime) {

            return sprintf('<del>%s</del> <ins>%s</ins>',
                $this->renderDateTime($key,$before[$key]),
                $this->renderDateTime($key,$data)
            );
        }
        if (is_array($data)) {
            $flatten = function($n){
                return isset($n['surnames']) ?
                    "$n[surnames], $n[given_names]":
                    "$n[lastname], $n[firstname]";
            };
            $names_now = array_map($flatten,$data);
            $names_before =  array_map($flatten,$before[$key]);
            $new = array_diff($names_now, $names_before);
            $deleted = array_diff($names_before,$names_now);
            $all = array_unique(array_merge($names_now,$names_before));
            $return = '';
            foreach($all as $n) {
                if (in_array($n,$deleted)) {
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

    public function renderDefendants(Array $data){
        $return = '';
        foreach ($data as $d) {
            $return .= "$d[surnames], $d[given_names]<br>";
        };

        return $return;
    }

    public function renderInterpreters(Array $data){
        $return = '';
        foreach ($data as $n) {
            $return .= "$n[lastname], $n[firstname]<br>";
        };

        return $return;
    }

    public function renderDateTime($key,\DateTime $obj) {
        switch ($key) {
            case 'time':
            case 'end_time':
            case 'submission_date':
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
            default:
                $format = 'r';
        }
        return $obj->format($format);
    }
    /*
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

        https://github.com/paulgb/simplediff
    */

    public function diff($old, $new){
        $matrix = array();
        $maxlen = 0;
        foreach($old as $oindex => $ovalue){
            $nkeys = array_keys($new, $ovalue);
            foreach($nkeys as $nindex){
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if($matrix[$oindex][$nindex] > $maxlen){
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    public function htmlDiff($old, $new){
        $ret = '';
        $diff = $this->diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach($diff as $k){
            if(is_array($k))
                $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                    (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
            else $ret .= $k . ' ';
        }
        return $ret;
    }
}
