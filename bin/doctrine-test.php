<?php
/* also not really part of the project, just screwing around and experimenting */

namespace InterpretersOffice\Entity;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Doctrine\Common\Collections\ArrayCollection;

echo "eat shit?\n";

$em = require __DIR__.'/../config/doctrine-bootstrap.php';

echo "trying to insert an interpreter...\n";

$interpreter = $em->getRepository('InterpretersOffice\Entity\Interpreter')->findOneBy(['lastname' => 'Mintz']);

if (! $interpreter) {
    exit("\nwhat?\n");

    $interpreter = new Interpreter();
    try {
        $hat_staff_interpreter = $em->getRepository('InterpretersOffice\Entity\Hat')->findOneBy(
            ['name' => 'staff court interpreter']);

        $interpreter->setLastname('Mintz')
            ->setFirstname('David')
            ->setHat($hat_staff_interpreter)
            ->setEmail('david@davidmintz.org')->setDob(new \DateTime('1958-05-26'));

        $spanish = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'Spanish']);

        $interpreter->addInterpreterLanguage(new InterpreterLanguage($interpreter, $spanish));
        $em->persist($interpreter);
        $em->flush();
    } catch (\Exception $e) {
        printf("caught exception %s: %s\n", get_class($e), $e->getMessage());
    } 
}

$spanish = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'Spanish']);
$french  = $em->getRepository('InterpretersOffice\Entity\Language')->findOneBy(['name' => 'French']);

$existing_languages = $interpreter->getInterpreterLanguages();

echo count($existing_languages), " languages at the moment\n";

system('echo "select * from interpreters_languages" | mysql -t office');


if (count($existing_languages)) {
    echo "removing all, adding 2 new...\n";
    foreach ($existing_languages as $obj) { 
        $interpreter->removeInterpreterLanguage($obj);          
    }
    $interpreter->removeInterpreterLanguages($existing_languages);
    $em->flush();
    echo("\ninterpreter id is: ". $interpreter->getId());
    // nothing works. how about...
    // foreach ($existing_languages as $obj) { $em->remove($obj);    }
    
    $count_after_removal =  $interpreter->getInterpreterLanguages()->count();
    echo("after removal count is: $count_after_removal\n");
    echo "database:\n";
    system('echo "select * from interpreters_languages" | mysql -t office');
    
    echo "attempting insert...\n";
    $interpreter->addInterpreterLanguage(new InterpreterLanguage($interpreter,$spanish));
    $interpreter->addInterpreterLanguage(new InterpreterLanguage($interpreter,$french));
    $em->flush();
    echo "database:\n";
    system('echo "select * from interpreters_languages" | mysql -t office');
}

printf("count is now %d\n",count($interpreter->getInterpreterLanguages()));
exit(0);


$interpreter = $em->getRepository('InterpretersOffice\Entity\Interpreter')->findOneBy(['lastname' => 'Mintz']);

if (!$interpreter) {
    echo 'no Mintz among the interpreters';
}

$interpreterLanguage = $interpreter->getInterpreterLanguages()[0];

//$interpreter->removeInterpreterLanguage($interpreterLanguage);

$em->flush();

printf("interpreter-language count is now %d\n",count($interpreter->getInterpreterLanguages()));



exit();
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
