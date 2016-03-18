<?php
namespace Application\Entity;
$em = require('./doctrine-bootstrap.php');

printf("we have a %s\n",get_class($em));

$interpreter = new Interpreter;

$interpreter
	->setLastname('Mintz')
	->setDob(new \DateTime('1958-05-26'))
	->setEmail('david@davidmintz.org')
	->setPhone('201 978-0608');	
	$em->persist($interpreter);
	$em->flush();

exit("all good\n");

