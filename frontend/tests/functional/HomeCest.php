<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

/**
 * Class HomeCest
 * @package frontend\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class HomeCest
{
    public function checkOpen(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->see('PROJECT NAME');
        $I->seeLink('О нас');
        $I->click('О нас');
        $I->see('This is the About page.');
    }
}