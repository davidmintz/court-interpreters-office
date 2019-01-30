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
        // there is a diff
        if (is_string($data) ) {//&& false === strstr($key,'comments')
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
}
