<?php

namespace Majordome\Rule;

use Majordome\Resource\ResourceInterface;

interface RuleEngineInterface
{
    /**
     * @param RuleInterface $rule
     */
    public function addRule(RuleInterface $rule);

    /**
     * @return RuleInterface[]
     */
    public function getRules();

    /**
     * In case a given resource wasn't valid in isValid() method, return the rule that caused this invalidation
     *
     * @return RuleInterface|null
     */
    public function getInvalidatedRule();

    /**
     * Decide if the given resource is valid or not by applying the list of given rules
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isValid(ResourceInterface $resource);
}
