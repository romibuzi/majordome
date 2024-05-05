<?php

namespace Majordome\Rule;

use Majordome\Resource\Resource;

interface RuleEngine
{
    /**
     * @param Rule $rule
     */
    public function addRule(Rule $rule): void;

    /**
     * @param Rule[] $rules
     */
    public function addRules(array $rules): void;

    /**
     * @return Rule[]
     */
    public function getRules(): array;

    /**
     * In case a given resource wasn't valid in isValid() method, return the rule that caused this invalidation
     *
     * @return Rule|null
     */
    public function getInvalidatedRule(): ?Rule;

    /**
     * Decide if the given resource is valid or not by applying the list of given rules
     *
     * @param Resource $resource
     *
     * @return bool
     */
    public function isValid(Resource $resource): bool;
}
