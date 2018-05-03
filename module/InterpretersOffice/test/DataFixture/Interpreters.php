<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use InterpretersOffice\Entity;

use Zend\Log;


class Interpreters extends AbstractFixture
{
    public function load(ObjectManager $objectManager)
    {
        $resolver = $objectManager->getConfiguration()->getEntityListenerResolver();
        $listener = new Entity\Listener\InterpreterEntityListener();
        // keep from blowing up if we are outside the MVC context
        if (! $listener->getLogger()) {
            $logger = new Log\Logger();
            $logger->addWriter(new Log\Writer\Noop);
            $listener->setLogger($logger);
        }
        $resolver->register($listener);

        $data = [
            [
                'lastname' => 'Snyetsky',
                'firstname' => 'Vladmimir',
                'middlename' => '',
                'languages'=> ['Russian'],
                'email' => 'vladsnyet@example.ru',
                'contractor' => true,
            ],
            [
                'lastname' => 'Fulana',
                'firstname' => 'María',
                'middlename' => 'B.',
                'languages'=> ['Spanish','Italian'],
                'email' => 'fulana@somedomain.com',
                'contractor' => true,
            ],
            [
                'lastname' => 'de la France',
                'firstname' => 'Françoise',
                'middlename' => 'Z.',
                'languages'=> ['French'],
                'email' => 'france@french.fr',
                'contractor' => true,
            ],
            [
                'lastname'   => 'Mintz',
                'firstname'  => 'David',
                'middlename' => '',
                'languages'  => ['Spanish'],
                'email'      => 'david@davidmintz.org',
                'contractor' => false,
            ],
        ];
        $repo = $objectManager->getRepository(Entity\Language::class);
        foreach ($data as $interpreter) {
            $entity = new Entity\Interpreter();
            $what = $interpreter['contractor'] ? 'hat-contract-interpreter' :
                'hat-staff-interpreter';
            $hat = $this->getReference($what);
            $entity->setHat($hat)
                ->setActive(true)
                ->setFirstname($interpreter['firstname'])
                ->setLastname($interpreter['lastname'])
                ->setMiddlename($interpreter['middlename'])
                ->setEmail($interpreter['email']);
            foreach($interpreter['languages'] as $lang) {

                $language = $repo->findOneBy(['name'=>$lang]);
                $interpreterLanguage = (new Entity\InterpreterLanguage($entity, $language));
                if ('Spanish' == $lang) { $interpreterLanguage->setFederalCertification(true);}
                $entity->addInterpreterLanguage($interpreterLanguage);
            }
            $objectManager->persist($entity);
            if ('Mintz'==$interpreter['lastname']) {
                $mintz = $entity; // save a reference
            }
        }
        $objectManager->flush();
        $this->setReference('interpreter-mintz', $mintz);
    }
}
