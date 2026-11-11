<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use Throwable;

/**
 * @api
 *
 * @template-covariant T of Throwable
 */
final class ExceptionMatch
{
    public function __construct(
        /** @var T */
        private readonly Throwable $exception,
        /** @var CatchExceptionMappingNode<Throwable> */
        private readonly CatchExceptionMappingNode $node,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return CatchExceptionMappingNode<Throwable>
     *
     * @internal
     */
    public function getNode(): CatchExceptionMappingNode
    {
        return $this->node;
    }
}
