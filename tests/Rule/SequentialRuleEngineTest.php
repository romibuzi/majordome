<?php

namespace Majordome\Tests\Rule;

use Majordome\Rule\SequentialRuleEngine;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SequentialRuleEngineTest extends TestCase
{
    use ProphecyTrait;

    public function testAddRules()
    {
        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\Rule');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\Rule');

        $sequentialRuleEngine = new SequentialRuleEngine();
        $sequentialRuleEngine->addRule($rule1->reveal());
        $sequentialRuleEngine->addRule($rule2->reveal());

        $rules = $sequentialRuleEngine->getRules();

        $this->assertSame(2, count($rules));
        $this->assertContains($rule1->reveal(), $rules);
        $this->assertContains($rule2->reveal(), $rules);

        foreach ($rules as $rule) {
            $this->assertInstanceOf('Majordome\Rule\Rule', $rule);
        }
    }

    public function testIsValid()
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');

        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\Rule');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\Rule');

        // both rules will indicates that the given resource is valid
        $rule1->isValid($resource->reveal())->willReturn(true)->shouldBeCalled();
        $rule2->isValid($resource->reveal())->willReturn(true)->shouldBeCalled();

        $sequentialRuleEngine = new SequentialRuleEngine();
        $sequentialRuleEngine->addRule($rule1->reveal());
        $sequentialRuleEngine->addRule($rule2->reveal());

        $result = $sequentialRuleEngine->isValid($resource->reveal());
        $this->assertTrue($result);
    }

    public function testIsInvalid()
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\Resource');

        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\Rule');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\Rule');

        // rule1 will indicates that the given resource is invalid
        $rule1->isValid($resource->reveal())->willReturn(false)->shouldBeCalled();
        // rule2 shouldn't be executed, as rule1 already indicates the resource as invalid
        $rule2->isValid($resource->reveal())->willReturn(true)->shouldNotBeCalled();

        $sequentialRuleEngine = new SequentialRuleEngine();
        $sequentialRuleEngine->addRule($rule1->reveal());
        $sequentialRuleEngine->addRule($rule2->reveal());

        $result = $sequentialRuleEngine->isValid($resource->reveal());

        $this->assertFalse($result);
        $this->assertSame($rule1->reveal(), $sequentialRuleEngine->getInvalidatedRule());
    }
}
