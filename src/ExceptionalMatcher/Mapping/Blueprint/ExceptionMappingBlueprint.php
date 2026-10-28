<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Blueprint;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;

/**
 * @internal
 *
 * @template TEmbracingNode of ExceptionMappingNode
 *
 * @template-covariant TResultingNode of ExceptionMappingNode
 */
interface ExceptionMappingBlueprint
{
    /**
     * @param TEmbracingNode $embracingNode
     *
     * @return TResultingNode
     */
    public function bind(ExceptionMappingNode $embracingNode): ExceptionMappingNode;
}
