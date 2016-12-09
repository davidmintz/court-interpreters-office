<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

class InterpreterLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $interpreter = new Entity\Interpreter();
        $interpreter
            ->setLastname('Mintz')
            ->setFirstname('David')
                ->setActive(true)
            ->setEmail('david@davidmintz.org')
            ->setDob(new \DateTime('1958-05-26'))
            ->setHat(
                $objectManager->getRepository('InterpretersOffice\Entity\Hat')
            ->findOneBy(['name' => 'staff Court Interpreter'])
            );
        $spanish = $objectManager->getRepository('InterpretersOffice\Entity\Language')
            ->findOneBy(['name' => 'Spanish']);
        $interpreterLanguage = (new Entity\InterpreterLanguage($interpreter, $spanish))
            ->setFederalCertification(true);
        $interpreter->addInterpreterLanguage($interpreterLanguage);

        $objectManager->persist($interpreter);
        $objectManager->flush();
    }
}
