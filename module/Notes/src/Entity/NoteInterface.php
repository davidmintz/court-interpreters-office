<?php /** module/Notes/src/Entity/NoteInterface.php */

namespace InterpretersOffice\Admin\Notes\Entity;
use DateTime;

/**
 * interface for MOTD|MOTW entities
 */
interface NoteInterface
{
    /**
     * gets content
     * 
     * @return string
     */
    public function getContent(): string;

    /**
     * gets date
     * 
     * @return DateTIme
     */
    public function getDate(): ?DateTime;

}
