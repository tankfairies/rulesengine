<?php

namespace Tests;

use \Codeception\Test\Unit;
use ReflectionProperty;
use Tankfairies\RulesEngine\RulesEngine;
use Tankfairies\RulesEngine\RulesException;

class RulesEngineTest extends Unit
{

    /**
     * @var RulesEngine
     */
    private $rulesEngine;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->rulesEngine = new RulesEngine('tests/_output/');
    }

    protected function _after()
    {
        $this->rulesEngine = null;
    }

    protected function cleanup($rule)
    {
        $file = "Rule" . hash('ripemd160', $rule).'.php';
        unlink(__DIR__.'/../_output/'.$file);
    }

    public function testPathIsSet()
    {
        $reflection = new ReflectionProperty('Tankfairies\RulesEngine\RulesEngine', 'path');
        $reflection->setAccessible(true);
        $this->assertEquals('tests/_output/', $reflection->getValue($this->rulesEngine));
    }

    public function testSetValidRule()
    {
        $rule = 'var == val';
        $this->rulesEngine->setRule($rule);
        $this->cleanup($rule);
    }

    public function testSetInvalidRule()
    {
        $this->tester->expectException(
            new RulesException('Invalid rule format'),
            function () {
                $this->rulesEngine->setRule('var==val');
            }
        );
    }

    public function testRuleNotSet()
    {
        $this->tester->expectException(
            new RulesException('Rule not set'),
            function () {
                $this->rulesEngine->setRule('');
            }
        );
    }

    public function testRuleNotSet2()
    {
        $this->tester->expectException(
            new RulesException('Rule not set'),
            function () {
                $this->rulesEngine->evaluate(['var' => 'bob']);
            }
        );
    }

    protected function ruleTests($rule, $values)
    {
        $this->rulesEngine->setRule($rule);
        $result = $this->rulesEngine->evaluate($values);
        $this->cleanup($rule);
        return $result;
    }

    public function testEvaluate1()
    {
        $result = $this->ruleTests('var == val', ['var' => 21, 'val' => 21]);
        $this->assertTrue($result);
    }

    public function testEvaluate2()
    {
        $result = $this->ruleTests('var != val', ['var' => 21, 'val' => 5]);
        $this->assertTrue($result);
    }

    public function testEvaluate3()
    {
        $result = $this->ruleTests('var > val', ['var' => 22, 'val' => 21]);
        $this->assertTrue($result);
    }

    public function testEvaluate4()
    {
        $result = $this->ruleTests('var < val', ['var' => 20, 'val' => 21]);
        $this->assertTrue($result);
    }

    public function testEvaluate5()
    {
        $result = $this->ruleTests('var >= val', ['var' => 22, 'val' => 21]);
        $this->assertTrue($result);
    }

    public function testEvaluate6()
    {
        $result = $this->ruleTests('var <= val', ['var' => 20, 'val' => 21]);
        $this->assertTrue($result);
    }

    public function testEvaluate7()
    {
        $result = $this->ruleTests('var == 44', ['var' => 44]);
        $this->assertTrue($result);
    }

    public function testEvaluate8()
    {
        $result = $this->ruleTests('var == val OR var == 22', ['var' => 20, 'val' => 20]);
        $this->assertTrue($result);
    }


    public function testEvaluate9()
    {
        $result = $this->ruleTests('var IN val', ['var' => 22, 'val' => [21, 22, 23]]);
        $this->assertTrue($result);
    }

    public function testEvaluate10()
    {
        $result = $this->ruleTests('var !IN val', ['var' => 20, 'val' => [21, 22, 23]]);
        $this->assertTrue($result);
    }

    public function testEvaluate11()
    {
        $result = $this->ruleTests('var1 == 44 AND var2 > 500 OR var3 == 40', ['var1' => 44, 'var2' => 501, 'var3' => 40]);
        $this->assertTrue($result);
    }

    public function testEvaluate12()
    {
        $result = $this->ruleTests('var IN val AND var2 == "yes"', ['var' => 22, 'val' => [21, 22, 23], 'var2' => 'yes']);
        $this->assertTrue($result);
    }

    public function testEvaluate13()
    {
        $this->rulesEngine->setRule('var == val');

        $result = $this->rulesEngine->evaluate(['var' => 'yes', 'val' => 'yes']);
        $this->assertTrue($result);

        $result = $this->rulesEngine->evaluate(['var' => 'yes', 'val' => 'no']);
        $this->assertFalse($result);

        $result = $this->rulesEngine->evaluate(['var' => 'yes', 'val' => 'maybe']);
        $this->assertFalse($result);

        $this->cleanup('var == val');
    }

    public function testEvaluate14()
    {
        $result = $this->ruleTests('var IN [12, 23]', ['var' => 12]);
        $this->assertTrue($result);
    }

    public function testEvaluate15()
    {
        $result = $this->ruleTests('var IN [12, 23]', ['var' => 1]);
        $this->assertFalse($result);
    }

    public function testEvaluate16()
    {
        $result = $this->ruleTests('var1 == 100 XOR var2 IN [12, 46]', ['var1' => 90, 'var2' => 12]);
        $this->assertTrue($result);
    }

    public function testEvaluate17()
    {
        $result = $this->ruleTests('var == "bob"', ['var' => 'bob']);
        $this->assertTrue($result);
    }

    public function testEvaluate18()
    {
        $result = $this->ruleTests('var <= \'bob\'', ['var' => 'bob']);
        $this->assertTrue($result);
    }

    public function testEvaluate19()
    {
        $result = $this->ruleTests('var == 100 XOR group IN [123456, 456456]', ['var' => '100', 'group' => 12121]);
        $this->assertTrue($result);
    }

    public function testRuleNotSet20()
    {
        $this->tester->expectException(
            new RulesException('Unknown condition in rule'),
            function () {
                $this->ruleTests('var ==> 100', ['var' => 100]);
            }
        );
    }

    public function testRuleNotSet21()
    {
        $this->tester->expectException(
            new RulesException('Unknown condition in rule'),
            function () {
                $this->ruleTests('var => 100', ['var' => 100]);
            }
        );
    }
}
