[![Latest Stable Version](https://poser.pugx.org/tankfairies/rulesengine/v/stable)](https://packagist.org/packages/tankfairies/rulesengine)
[![Total Downloads](https://poser.pugx.org/tankfairies/rulesengine/downloads)](https://packagist.org/packages/tankfairies/rulesengine)
[![Latest Unstable Version](https://poser.pugx.org/tankfairies/rulesengine/v/unstable)](https://packagist.org/packages/tankfairies/rulesengine)
[![License](https://poser.pugx.org/tankfairies/rulesengine/license)](https://packagist.org/packages/tankfairies/rulesengine)
[![Build Status](https://travis-ci.com/tankfairies/rulesengine.svg?branch=master)](https://travis-ci.com/tankfairies/rulesengine)


# Rulesengine
Converts conditional statements into optimised asset rules.
This gives the ability to store rules in files or databases.

**It is important that rules are correctly validated if the rules can be user defined.**

**This is because the rules are converted into code to make them fast**

This is supported and looked after, if there's and functionality you'd like to see; let me know and I'll look into it.

## Installation

Install with [Composer](https://getcomposer.org/):

```bash
composer require tankfairies/rulesengine
```

## Usage

Instantiate a new instance of the library:

```php
use Tankfairies\Rulesengine\RulesEngine;

$this->rulesEngine = new RulesEngine('storage/rules');
$rulesEngine->setRule('var == 21');
$result = $rulesEngine->evaluate(['var' => 21]);
```

The following operators are available to use in rules: -

* AND
* OR 
* XOR

The following conditions are available to use in rules: -

* ==
* !-
* <=
* \>=
* <
* \>
* IN
* !IN

## Sample Rules

```php
var == val OR var == 22
['var' => 20, 'val' => 20]
```

```php
var !IN val
['var' => 20, 'val' => [21, 22, 23]]
```

```php
var IN val AND var2 == "yes"
['var' => 22, 'val' => [21, 22, 23], 'var2' => 'yes']
```

```php
var == 100 XOR group IN [123456, 456456]
['var' => '100', 'group' => 12121]
```

## Copyright and license

The tankfairies/rulesengine library is Copyright (c) 2019 Tankfairies (https://tankfairies.com) and licensed for use under the MIT License (MIT).
