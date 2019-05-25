<?php
/**
 * Copyright (c) 2019 Tankfairies
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/tankfairies/rulesengine
 */

namespace RulesEngine;

use Exception;

/**
 * Class RulesException
 *
 * @package RulesEngine
 */
class RulesException extends Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
