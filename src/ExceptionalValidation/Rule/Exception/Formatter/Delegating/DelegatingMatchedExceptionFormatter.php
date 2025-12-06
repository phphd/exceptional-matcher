<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements MatchedExceptionFormatter<Throwable,mixed>
 */
final readonly class DelegatingMatchedExceptionFormatter implements MatchedExceptionFormatter
{
    /**
     * @api
     *
     * @template T of MatchedExceptionFormatter
     *
     * @param ContainerInterface<class-string<T>,T> $formatterRegistry
     */
    public function __construct(
        private ContainerInterface $formatterRegistry,
    ) {
    }

    /** @template T of MatchedExceptionFormatter */
    public function format(MatchedException $matchedException): array // @phpstan-ignore method.templateTypeNotInParameter
    {
        $matchedRule = $matchedException->getRule();

        /** @var class-string<T> $formatterId */ // FIXME: use real type
        $formatterId = $matchedRule->getFormatterId();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new LogicException('Matched Exception Formatter not found: '.$formatterId);
        }

        /** @var T $exceptionFormatter */
        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        return $exceptionFormatter->format($matchedException);
    }
}
