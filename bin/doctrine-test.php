<?php

namespace Application\Entity;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

echo "eat shit?\n";

$em = require __DIR__.'/../config/doctrine-bootstrap.php';

echo "trying to insert an interpreter...\n";

$interpreter = new Interpreter();
try {
    $hat_staff_interpreter = $em->getRepository('Application\Entity\Hat')->findOneBy(
        ['type' => 'staff interpreter']);

    $interpreter->setLastname('Mintz')
        ->setFirstname('David')
        ->setHat($hat_staff_interpreter)
        ->setEmail('david@davidmintz.org')->setDob(new \DateTime('1958-05-26'));

    $spanish = $em->getRepository('Application\Entity\Language')->findOneBy(['name' => 'Spanish']);

    $interpreter->addInterpreterLanguage(new InterpreterLanguage($interpreter, $spanish));
    $em->persist($interpreter);
    $em->flush();
} catch (\Exception $e) {
    printf("caught exception %s: %s\n", get_class($e), $e->getMessage());
}

exit("ok\n");

$interpreter = $em->getRepository('Application\Entity\Interpreter')->findOneBy(['lastname' => 'Mintz']);

if (!$interpreter) {
    echo 'no Mintz among the interpreters';
}

$interpreterLanguage = $interpreter->getInterpreterLanguages()[0];

$interpreter->removeInterpreterLanguage($interpreterLanguage);

$em->flush();

exit("\n");

//$language = new Language();
//$language->setName('Spanish');
//$em->persist($language);

//$hat = $em->getRepository('Application\Entity\Hat')->findOneBy(['type'=>'staff interpreter']);

$interpreterLanguage = new InterpreterLanguage();
$interpreter = new Interpreter();
$interpreter
        ->setHat($hat)
        ->setFirstname('David')
        ->setDob(new \DateTime('1958-05-26'))
        ->setLastname('Mintz');
$interpreterLanguage->setLanguage($language)->setInterpreter($interpreter);
$interpreter->addInterpreterLanguage($interpreterLanguage);
$em->persist($interpreter);
$em->flush();

exit("\n");
try {
    $interpreter = new Interpreter();
    $hat = new Hat();
    $hat->setType('staff interpreter');
    $interpreter->setEmail('david@davidmintz.org')
        ->setFirstname('David')
        ->setLastname('Mintz')
        ->setHat($hat)
                ->setDob(new \DateTime('1958-05-26'));

    $user = new User();
    $user
        ->setPassword('boink');
        //->setEmail('david@davidmintz.org')
        //->setFirstname('David')
        //->setLastname('Mintz')
        //->setHat($em->getRepository('Application\Entity\Hat')->findOneBy(['type'=>'contract interpreter']));
    $user->setPerson($interpreter);
    $em->persist($user);
    $em->persist($hat);
    $em->persist($interpreter);
    $em->flush();
} catch (UniqueConstraintViolationException $e) {
    echo $e->getMessage();
}

/*
$interpreter = new Interpreter;
$person = new Person;
$person->setLastname('Mintz')->setLastname('Mintz')->setFirstname('David')->setEmail('david@davidmintz.org');
$hat = $em->getRepository('Application\Entity\Hat')->findOneBy(['type'=>'contract interpreter']);
$interpreter->setDob(new \DateTime('1958-05-26'));
$interpreter->setPerson($person);
$person->setHat($hat);
$em->persist($interpreter);
$em->persist($person);
$em->flush();
*/
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
