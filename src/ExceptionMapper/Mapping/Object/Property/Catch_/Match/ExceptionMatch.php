<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 */
final class ExceptionMatch
{
    public function __construct(
        /** @var T */
        private readonly Throwable $exception,
        /** @var CatchExceptionMappingNode<T> */
        private readonly CatchExceptionMappingNode $node,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return CatchExceptionMappingNode<T>
     *
     * @internal
     */
    public function getNode(): CatchExceptionMappingNode
    {
        return $this->node;
    }
}
