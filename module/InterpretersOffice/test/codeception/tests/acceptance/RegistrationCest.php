<?php


class RegistrationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function fuckYouTest(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('InterpretersOffice');
        $I->click("create an account");
        $I->amOnPage('/user/register');
        $I->see('account registration');
    }
}
