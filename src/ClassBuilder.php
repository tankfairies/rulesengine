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
 * Class ClassBuilder
 *
 * @package RulesEngine
 */
class ClassBuilder
{
    private $path;
    private $className;
    private $ruleString;
    private $namespace;

    /**
     * ClassBuilder constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string $className
     * @return ClassBuilder
     */
    public function setClassName(string $className): self
    {
        $this->className = $className;
        return $this;
    }

    /**
     *
     *
     * @param string $rule
     * @return ClassBuilder
     */
    public function setRule(string $rule): self
    {
        $this->ruleString = $rule;
        return $this;
    }

    /**
     * Sets the namespace
     *
     * @param string $namespace
     * @return ClassBuilder
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Builds the class
     *
     * @return ClassBuilder
     * @throws RulesException
     */
    public function build(): self
    {
        if (empty($this->path)) {
            throw new RulesException('Path not set');
        }

        if (empty($this->className)) {
            throw new RulesException('Classname not set');
        }

        if (empty($this->namespace)) {
            throw new RulesException('Namespace not set');
        }

        if (empty($this->ruleString)) {
            throw new RulesException('Rule not set');
        }

        $classFile = $this->path . '/' . $this->className . ".php";

        //builds the function that does the assert
        $function = $this->buildAssertFunction();

        //file contents
        $output = "<?php\n"
            . "namespace {$this->namespace};\n"
            . "class {$this->className} implements \\Tankfairies\\RulesEngine\\RuleInterface\n"
            . "{\n {$function} }";

        //writes the file to disk
        file_put_contents($classFile, $output);

        return $this;
    }

    /**
     * Builds the function.
     *
     * @return string
     * @throws RulesException
     */
    private function buildAssertFunction(): string
    {
        //builds a single depth condition list tree
        $ruleTree = $this->operatorsAndConditions($this->ruleString);

        $conditions = '';
        $sets = [];

        foreach ($ruleTree as $ruleSet) {
            $firstValue = $this->embeddedContextValue($ruleSet['firstValue']);
            $secondValue = $this->embeddedContextValue($ruleSet['secondValue']);

            $conditions .= " {$ruleSet['join']} ";

            if (in_array($ruleSet['condition'], ['IN', '!IN'])) {
                if ($ruleSet['condition'] === '!IN') {
                    $conditions .= "!";
                }

                $return = $this->assert($firstValue);
                $sets[] = $return['sets'];
                $needle = $return['item'];

                $return = $this->assert($secondValue);
                $sets[] = $return['sets'];
                $haystack = $return['item'];

                $conditions .= "in_array({$needle}, {$haystack}, true)";
            } else {
                $return = $this->assert($firstValue);
                $sets[] = $return['sets'];
                $conditions .= "{$return['item']} {$ruleSet['condition']} ";

                $return = $this->assert($secondValue);
                $sets[] = $return['sets'];
                $conditions .= $return['item'];
            }
        }

        $conditions = trim($conditions);

        return "public function assert(array \$context): bool\n{\n"
            . implode('', array_unique($sets))
            . "    return ({$conditions});\n"
            . "}\n";
    }

    /**
     * Returns the assert details
     *
     * @param array $value
     * @return array
     */
    private function assert(array $value): array
    {
        $value['field'] = trim($value['field']);
        $return = [];

        if ($value['field'] !== $value['value']) {
            $return['sets'] = "\${$value['field']} = \$context['{$value['field']}'];\n";
            $return['item'] = "\${$value['field']}";
        } else {
            $return['sets'] = '';
            $return['item'] = $value['field'];
        }

        return $return;
    }

    /**
     * Sets the field value / name handler
     *
     * @param string $field
     * @return array
     */
    private function embeddedContextValue(string $field): array
    {
        $value = '';
        $isNumeric = is_numeric(str_replace(['"', "'"], "", $field));

        if ($isNumeric
            || strpos($field, '"') !== false
            || strpos($field, "'") !== false
        ) {
            //string or numeric
            if ($isNumeric) {
                $field = $value = str_replace(['"', "'"], "", $field);
            } else {
                $value = $field;
            }
        } elseif (strpos($field, '[') !== false) {
            //array
            $field = $value = '[' . str_replace(["[", "]"], "", $field) . ']';
        }

        return ['field' => $field, 'value' => $value];
    }

    /**
     * Breaks the rule into part separated by operator
     * and then converts the conditional statements into a rule tree
     *
     * @param string $rule
     * @return array
     * @throws RulesException
     */
    private function operatorsAndConditions(string $rule): array
    {
        //splits on operators
        $output = preg_split("/ (AND|OR|XOR) /", $rule, -1, PREG_SPLIT_OFFSET_CAPTURE);

        $evaluations = [];
        foreach ($output as $part) {
            $t['condition'] = $part[0];
            $operator = substr($rule, $part[1]-5, 6);

            $t['join'] = "";
            if (stripos($operator, "AND") !== false) {
                $t['join'] = "&&";
            } elseif (stripos($operator, "XOR") !== false) {
                $t['join'] = "XOR";
            } elseif (stripos($operator, "OR") !== false) {
                $t['join'] = "||";
            }
            $evaluations[] = $t;
        }

        //converts each evaluation into a rule tree
        $ruleTree = [];
        foreach ($evaluations as $evaluation) {
            $parts = explode(' ', $evaluation['condition']);

            if (!isset($parts[1])) {
                throw new RulesException('Invalid rule format');
            }

            if ($this->strposa(trim($parts[1]))) {
                $ruleSet['evaluation'] = $evaluation['condition'];
                $ruleSet['condition'] = trim($parts[1]);
                $arr = explode(' '.$ruleSet['condition'].' ', $evaluation['condition']);
                $ruleSet['firstValue'] = $arr[0];
                $ruleSet['secondValue'] = $arr[1];
                $ruleSet['join'] = $evaluation['join'];

                $ruleTree[] = $ruleSet;
            } else {
                throw new RulesException('Unknown condition in rule');
            }
        }

        return $ruleTree;
    }

    /**
     * Locates a match from an array of options
     *
     * @param string $haystack
     * @return bool
     */
    private function strposa(string $haystack): bool
    {
        $needle = ['==', '!=', '>=', '<=', '<', '>', '!IN', 'IN'];
        foreach ($needle as $query) {
            if (strpos($haystack, $query) !== false && strlen($query) == strlen($haystack)) {
                return true;
            }
        }
        return false;
    }
}
