<?php

namespace Majordome\Rule;

use Majordome\Resource\Resource;

/**
 * The rule implementing a logic to decide if a resource should be considered as invalid (so as a candidate of cleanup)
 */
interface Rule
{
    /**
     * Decides whether the resource should be a candidate of cleanup based on the underlying rule
     *
     * @param Resource $resource
     *
     * @return bool true if the resource is valid and is not for cleanup, false otherwise
     */
    public function isValid(Resource $resource): bool;

    /**
     * Return the name of the rule, generally the name of the Rule class
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Return a small description of what the Rule does
     *
     * @return string
     */
    public static function getDescription(): string;
}
