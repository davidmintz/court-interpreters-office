<?php
/**
 * module/Admin/src/Form/Filter/Docket.php
 */

namespace InterpretersOffice\Admin\Form\Filter;

use Zend\Filter\FilterInterface;

/**
 *
 * filters a docket number for storage in database
 */
class Docket implements FilterInterface
{
    
    const REGEX = '/^ *((?:19|20)?\d{2})[\- ]*(CR|CI?V|M(?:AG|ISC|J)?)(?:IM)?[ \-]*(\d+){1,5} *$/i';
    
    function filter($docket) {
        
        if (!$docket)  { return $docket; }
        $m = array();
        if (!preg_match(self::REGEX, $docket, $m)) {
            return $docket;
        }
        list($year, $flav, $num) = array_slice($m, 1);
        if (! (int) $num) {
            // bogus number, should just be empty string
            // return '';
        }
        $flav = strtoupper($flav);
        if ('M' == $flav or 'MJ' == $flav) {
            $flav = 'MAG';
        } elseif ('CV' == $flav) {
            $flav = 'CIV';
        }
        if (strlen($year) == 2) {
            $year += ($year >= 50 ? 1900 : 2000);
        }
        return $year .'-' . $flav . '-' . sprintf('%04d', $num);
    }
    
}