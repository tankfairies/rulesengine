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
 * Interface RuleInterface
 *
 * @package RulesEngine
 */
interface RuleInterface
{
    public function assert(array $context): bool;
}
