<?php

namespace Tests\unit;

use \Codeception\Test\Unit;
use Tankfairies\RulesEngine\RulesException;

class RulesExceptionTest extends Unit
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testException()
    {
        $this->tester->expectThrowable(
            new RulesException('this is a test'),
            function () {
                throw new RulesException('this is a test');
            }
        );
    }
}
