<?php

namespace Majordome\Rule;

use Majordome\Resource\Resource;

class SequentialRuleEngine implements RuleEngine
{
    /** @var Rule[] */
    private array $rules = [];

    /** @var Rule|null */
    private ?Rule $invalidatedRule = null;

    /**
     * {@inheritDoc}
     */
    public function addRule(Rule $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * {@inheritDoc}
     */
    public function addRules(array $rules): void
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * {@inheritDoc}
     */
    public function getInvalidatedRule(): ?Rule
    {
        return $this->invalidatedRule;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(Resource $resource): bool
    {
        $this->invalidatedRule = null;

        foreach ($this->rules as $rule) {
            if (!$rule->isValid($resource)) {
                $this->invalidatedRule = $rule;
                return false;
            }
        }

        return true;
    }
}
