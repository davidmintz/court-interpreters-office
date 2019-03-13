<?php

namespace ApplicationTest\DataFixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use InterpretersOffice\Entity;

class InterpreterLoader implements FixtureInterface
{
    public function load(ObjectManager $objectManager)
    {
        $AO =  $objectManager->getRepository('InterpretersOffice\Entity\LanguageCredential')
                ->findOneBy(['abbreviation' => 'AO']);
        $PQ =  $objectManager->getRepository('InterpretersOffice\Entity\LanguageCredential')
                ->findOneBy(['abbreviation' => 'PQ']);
        $interpreter = new Entity\Interpreter();
        $staff_hat = $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                ->findOneBy(['name' => 'staff court interpreter']);
        $contractor_hat = $objectManager->getRepository('InterpretersOffice\Entity\Hat')
                ->findOneBy(['name' => 'contract court interpreter']);

        $interpreter
            ->setLastname('Mintz')
            ->setFirstname('David')
                ->setActive(true)
            ->setEmail('david@davidmintz.org')
            //->setDob(new \DateTime('1958-05-26'))
                ->setDob('1958-05-26')
            ->setHat($staff_hat);
        $spanish = $objectManager->getRepository('InterpretersOffice\Entity\Language')
            ->findOneBy(['name' => 'Spanish']);
        $russian = $objectManager->getRepository('InterpretersOffice\Entity\Language')
            ->findOneBy(['name' => 'Russian']);
        $interpreterLanguage = (new Entity\InterpreterLanguage($interpreter, $spanish))
            ->setLanguageCredential($AO);
        $interpreter->addInterpreterLanguage($interpreterLanguage);

        $objectManager->persist($interpreter);

        $other_interpreter = new Entity\Interpreter();
        $other_interpreter->setLastname('Somebody')
            ->setFirstname('Margarita')
                ->setActive(true)
            ->setEmail('m.somebody@nysd.uscourts.gov')
            //->setDob(new \DateTime('1964-04-21'))
             ->setDob('1964-04-21')
            ->setHat($staff_hat);
        $other_interpreter->addInterpreterLanguage(
            (new Entity\InterpreterLanguage($other_interpreter, $spanish))->setLanguageCredential($AO));
        $objectManager->persist($other_interpreter);

        // create a Russian interpreter

        $russian_interpreter =  (new Entity\Interpreter())
            ->setLastname('Grotsky')
            ->setFirstname('Svetlana')
            ->setActive(true)
            ->setEmail('russian_interpreter@example.com')
            //->setDob(new \DateTime('1964-04-21'))
             ->setDob('1967-03-17')
            ->setHat($contractor_hat);
        $russian_interpreter->addInterpreterLanguage(
                (new Entity\InterpreterLanguage($russian_interpreter, $russian))->setLanguageCredential($PQ));


        $objectManager->persist($russian_interpreter);

        $objectManager->flush();
    }
}
