<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Blueprint;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;

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
