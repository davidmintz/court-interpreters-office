<?php
namespace Application\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

$em = require(__DIR__.'/../config/doctrine-bootstrap.php');

$interpreter = $em->find('Application\Entity\Interpreter',1);
echo get_class($interpreter);


exit("\n");
try {
	$interpreter = new Interpreter();
	$hat = new Hat();
	$hat->setType("staff interpreter");
	$interpreter->setEmail('david@davidmintz.org')
		->setFirstname('David')
		->setLastname('Mintz')
		->setHat($hat)
                ->setDob(new \DateTime('1958-05-26'));
                    
	$user = new User;
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

