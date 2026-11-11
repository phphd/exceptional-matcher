<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port;

use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatchList;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionReciprocal;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ExceptionMatchList>
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

    public function map(Throwable $exception, object $object): ?ExceptionMatchList
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

        return $reciprocal->getMatches();
    }
}
