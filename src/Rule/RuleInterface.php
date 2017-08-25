<?php

namespace Majordome\Rule;

use Majordome\Resource\ResourceInterface;

/**
 * The rule implementing a logic to decide if a resource should be considered as invalid (so as a candidate of cleanup)
 */
interface RuleInterface
{
    /**
     * Decides whether the resource should be a candidate of cleanup based on the underlying rule
     *
     * @param ResourceInterface $resource
     *
     * @return bool true if the resource is valid and is not for cleanup, false otherwise
     */
    public function isValid(ResourceInterface $resource);

    /**
     * Return the name of the rule, generally the name of the Rule class
     *
     * @return string
     */
    public function getName();

    /**
     * Return a small description of what the Rule does
     *
     * @return string
     */
    public function getDescription();
}
