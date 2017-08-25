<?php

namespace Majordome\Rule;

use Majordome\Resource\ResourceInterface;

class BasicRuleEngine implements RuleEngineInterface
{
    /** @var RuleInterface[] */
    private $rules = [];

    /** @var RuleInterface|null */
    private $invalidatedRule = null;

    /**
     * {@inheritDoc}
     */
    public function addRule(RuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritDoc}
     */
    public function getInvalidatedRule()
    {
        return $this->invalidatedRule;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(ResourceInterface $resource)
    {
        $valid = true;
        $this->invalidatedRule = null;

        foreach ($this->rules as $rule) {
            if (!$rule->isValid($resource)) {
                $valid = false;
                $this->invalidatedRule = $rule;

                break;
            }
        }

        return $valid;
    }
}
