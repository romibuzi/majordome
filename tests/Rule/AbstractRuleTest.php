<?php

namespace Majordome\Tests\Rule;

use Majordome\Rule\RuleInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractRuleTest extends TestCase
{
    /** @var RuleInterface */
    protected $rule;

    public function testGetName()
    {
        $name = $this->rule->getName();

        $this->assertNotEmpty($name, 'The Rule '.get_class($this->rule).' shoud have a name');
        $this->assertIsString($name);
    }

    public function testGetDescription()
    {
        $description = $this->rule->getDescription();

        $this->assertNotEmpty($description, 'The Rule '.get_class($this->rule).' shoud have a description');
        $this->assertIsString($description);
    }
}
