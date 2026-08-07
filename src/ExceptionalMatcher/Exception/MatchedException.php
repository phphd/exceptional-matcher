<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\CatchExceptionMappingNode;
use Throwable;

/**
 * @api
 *
 * @template-covariant T of Throwable
 */
final class MatchedException
{
    public function __construct(
        /** @var T */
        private readonly Throwable $exception,
        /** @var CatchExceptionMappingNode<Throwable> */
        private readonly CatchExceptionMappingNode $rule,
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
    public function getRule(): CatchExceptionMappingNode
    {
        return $this->rule;
    }
}
