<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\Delegating;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements MatchedExceptionFormatter<Throwable,mixed>
 */
final class DelegatingMatchedExceptionFormatter implements MatchedExceptionFormatter
{
    /**
     * @api
     *
     * @template T of MatchedExceptionFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $formatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter> $formatterRegistry
     */
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
    ) {
    }

    public function format(MatchedException $matchedException): array
    {
        $matchedRule = $matchedException->getRule();

        $formatterId = $matchedRule->getFormatterId();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new LogicException('Matched Exception Formatter not found: '.$formatterId);
        }

        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        /** @psalm-var MatchedExceptionFormatter<Throwable,mixed> $exceptionFormatter */
        return $exceptionFormatter->format($matchedException);
    }
}
