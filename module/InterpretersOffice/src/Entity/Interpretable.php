<?php /** module/InterpretersOffice/src/Entity/Interpretable.php */

namespace InterpretersOffice\Entity;

/**
 * here's Johnny!
 *
 */
interface Interpretable
{

    /**
     * gets interpreters
     *
     * @return Entity\Interpreter[]
     */
    public function getInterpreters(): Array;
}
