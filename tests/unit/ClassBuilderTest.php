<?php

namespace Tests;

use \Codeception\Test\Unit;
use ReflectionProperty;
use Tankfairies\RulesEngine\ClassBuilder;
use Tankfairies\RulesEngine\RulesException;

class ClassBuilderTest extends Unit
{

    private $classBuilder;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->classBuilder = new ClassBuilder('tests/_output/');
    }


    protected function _after()
    {
        $this->classBuilder = null;
    }

    protected function cleanup($rule)
    {
        $file = "Rule" . hash('ripemd160', $rule).'.php';
        unlink(__DIR__.'/../_output/'.$file);
    }

    public function testPathIsSet()
    {
        $reflection = new ReflectionProperty('Tankfairies\RulesEngine\ClassBuilder', 'path');
        $reflection->setAccessible(true);
        $this->assertEquals('tests/_output/', $reflection->getValue($this->classBuilder));
    }

    public function testSetClassName()
    {
        $this->classBuilder->setClassName('newClass');
        $reflection = new ReflectionProperty('Tankfairies\RulesEngine\ClassBuilder', 'className');
        $reflection->setAccessible(true);
        $this->assertEquals('newClass', $reflection->getValue($this->classBuilder));
    }

    public function testSetRule()
    {
        $this->classBuilder->setRule('var == 25');
        $reflection = new ReflectionProperty('Tankfairies\RulesEngine\ClassBuilder', 'ruleString');
        $reflection->setAccessible(true);
        $this->assertEquals('var == 25', $reflection->getValue($this->classBuilder));
    }

    public function testSetNamespace()
    {
        $this->classBuilder->setNamespace('NewNameSpace');
        $reflection = new ReflectionProperty('Tankfairies\RulesEngine\ClassBuilder', 'namespace');
        $reflection->setAccessible(true);
        $this->assertEquals('NewNameSpace', $reflection->getValue($this->classBuilder));
    }

    public function testBuildNoPath()
    {
        $this->classBuilder = new ClassBuilder('');
        $this->tester->expectException(
            new RulesException('Path not set'),
            function () {
                $this->classBuilder->build();
            }
        );
    }

    public function testBuildNoclassName()
    {
        $this->tester->expectException(
            new RulesException('Classname not set'),
            function () {
                $this->classBuilder->build();
            }
        );
    }

    public function testBuildNoNamespace()
    {
        $this->tester->expectException(
            new RulesException('Namespace not set'),
            function () {
                $this->classBuilder->setClassName('newClassName')->build();
            }
        );
    }

    public function testBuildNoRule()
    {
        $this->tester->expectException(
            new RulesException('Rule not set'),
            function () {
                $this->classBuilder->setClassName('NewClassName')->setNamespace('NewNamespace')->build();
            }
        );
    }

    public function testBuild()
    {
        $this->classBuilder
            ->setClassName('NewClassName')
            ->setNamespace('NewNamespace')
            ->setRule('var == 25')
            ->build();

        $file = __DIR__.'/../_output/NewClassName.php';

        $actual = file_get_contents($file);

        $expected = "<?php\n"
            ."namespace NewNamespace;\n"
            ."class NewClassName implements \Tankfairies\RulesEngine\RuleInterface\n"
            ."{\n"
            ." public function assert(array \$context): bool\n"
            ."{\n"
            ."\$var = \$context['var'];\n"
            ."    return (\$var == 25);\n"
            ."}\n"
            ." }";

        $this->assertEquals($expected, $actual);

        unlink($file);
    }
}
