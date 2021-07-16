<?php
/**
 * Copyright (c) 2019 Tankfairies
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/tankfairies/rulesengine
 */

namespace Tankfairies\RulesEngine;

/**
 * Class RulesEngine
 *
 * @package RulesEngine
 */
class RulesEngine
{
    private const NAMESPACE = 'RulesEngineCache';

    /**
     * @var RuleInterface
     */
    private $rule;
    private $path;


    /**
     * RulesEngine constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }


    /**
     *  rule examples
     *       var IN array
     *       var !IN array
     *
     *       var = val AND var2 = 44
     *       var = val AND var2 != 44
     *       var != 44
     *
     *       var = val AND var2 < 100
     *       var = val AND var2 >= 100
     *
     * @param string $ruleString
     * @return RulesEngine
     * @throws RulesException
     */
    public function setRule(string $ruleString): self
    {
        $className = "Rule" . hash('ripemd160', $ruleString);

        //build php class file and store to disk
        $classFile = $this->path .'/'. $className . ".php";
        if (!file_exists($classFile)) {
            (new ClassBuilder($this->path))
                ->setClassName($className)
                ->setNamespace(self::NAMESPACE)
                ->setRule($ruleString)
                ->build();
        }

        // include $classFile;
        if (!is_object($className)) {
            include_once $classFile;
        }

        //instantiated class from file
        $class = "\\" . self::NAMESPACE . "\\" . $className;
        $this->rule = new $class();

        return $this;
    }

    /**
     * Evaluates the rule using the context data.
     *
     * @param array $context
     * @return bool
     * @throws RulesException
     */
    public function evaluate(array $context): bool
    {
        if (empty($this->rule)) {
            throw new RulesException('Rule not set');
        }

        return $this->rule->assert($context);
    }
}
