<?php
/* also not really part of the project, just screwing around and experimenting */

namespace InterpretersOffice\Entity;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Doctrine\Common\Collections\ArrayCollection;

echo "eat shit?\n";

$em = require __DIR__.'/../config/doctrine-bootstrap.php';
        $person = new Person();
        $person->setFirstname('John')
                ->setLastname('Somebody')
                ->setEmail('john_somebody@lawfirm.com')
                ->setActive(true)
                ->setHat(
                    $em->getRepository('InterpretersOffice\Entity\Hat')
                        ->findOneBy(['name' => 'defense attorney'])
                );
        $em->persist($person);

        $em->flush();
exit("\nall good\n");

/*
$interpreter
    ->setLastname('Mintz')
    ->setDob(new \DateTime('1958-05-26'))
    ->setEmail('david@davidmintz.org')
    ->setPhone('201 978-0608')
    ->setLastname('Mintz')
    $em->persist($interpreter);
    $em->flush();
*/
