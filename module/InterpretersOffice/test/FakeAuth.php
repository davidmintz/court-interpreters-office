<?php

namespace ApplicationTest;

use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;

class FakeAuth implements AuthenticationServiceInterface
{
        /**
         * user Entity
         * @var Entity\User
         */
        private $user;

        /**
         * constructor
         *
         * @param EntityUser $user
         */
        public function __construct(Entity\User $user)
        {
            $this->user = $user;
        }

        public function hasIdentity()
        {
            return true;
        }
        public function getIdentity()
        {
            return (object)[
                'username'=>  $this->user->getUsername(),
                'id' => $this->user->getId(),
            ];
        }
        public function authenticate()
        {
            return new Result(1, $this->getIdentity());
        }

        public function clearIdentity()
        {
        }
}
