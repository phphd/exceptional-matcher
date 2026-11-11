<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Port;

use PhPhD\ExceptionalMatcher\ExceptionMapper;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<MatchedExceptionList>
 */
final class MainExceptionMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $printRegistry,
        private readonly ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function map(Throwable $exception, object $object): ?MatchedExceptionList
    {
        $print = $this->printRegistry->getMappingBlueprint($object::class);

        if (null === $print) {
            return null;
        }

        $matcher = $print->bind(object: $object);

        $exceptions = $this->exceptionUnwrapper->unwrap($exception);
        $reciprocal = new ExceptionReciprocal($exceptions);

        if (!$matcher->match($reciprocal)) {
            return null;
        }

        return $reciprocal->getMatchedExceptionList();
    }
}
