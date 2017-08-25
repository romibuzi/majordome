<?php

namespace Majordome\Tests\Rule;

use Majordome\Rule\BasicRuleEngine;

class BasicRuleEngineTest extends \PHPUnit_Framework_TestCase
{
    public function testAddRules()
    {
        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\RuleInterface');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\RuleInterface');

        $basicRuleEngine = new BasicRuleEngine();
        $basicRuleEngine->addRule($rule1->reveal());
        $basicRuleEngine->addRule($rule2->reveal());

        $rules = $basicRuleEngine->getRules();

        $this->assertSame(2, count($rules));
        $this->assertContains($rule1->reveal(), $rules);
        $this->assertContains($rule2->reveal(), $rules);

        foreach ($rules as $rule) {
            $this->assertInstanceOf('Majordome\Rule\RuleInterface', $rule);
        }
    }

    public function testIsValid()
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\ResourceInterface');

        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\RuleInterface');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\RuleInterface');

        // both rules will indicates that the given resource is valid
        $rule1->isValid($resource->reveal())->willReturn(true)->shouldBeCalled();
        $rule2->isValid($resource->reveal())->willReturn(true)->shouldBeCalled();

        $basicRuleEngine = new BasicRuleEngine();
        $basicRuleEngine->addRule($rule1->reveal());
        $basicRuleEngine->addRule($rule2->reveal());

        $result = $basicRuleEngine->isValid($resource->reveal());
        $this->assertTrue($result);
    }

    public function testIsInvalid()
    {
        $resource = $this->prophesize();
        $resource->willImplement('Majordome\Resource\ResourceInterface');

        $rule1 = $this->prophesize();
        $rule1->willImplement('Majordome\Rule\RuleInterface');

        $rule2 = $this->prophesize();
        $rule2->willImplement('Majordome\Rule\RuleInterface');

        // rule1 will indicates that the given resource is invalid
        $rule1->isValid($resource->reveal())->willReturn(false)->shouldBeCalled();
        // rule2 shouldn't be executed, as rule1 already indicates the resource as invalid
        $rule2->isValid($resource->reveal())->willReturn(true)->shouldNotBeCalled();

        $basicRuleEngine = new BasicRuleEngine();
        $basicRuleEngine->addRule($rule1->reveal());
        $basicRuleEngine->addRule($rule2->reveal());

        $result = $basicRuleEngine->isValid($resource->reveal());

        $this->assertFalse($result);
        $this->assertSame($rule1->reveal(), $basicRuleEngine->getInvalidatedRule());
    }
}
