<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements MatchedExceptionFormatter<Throwable,mixed>
 */
final class QualifiedMatchedExceptionFormatter implements MatchedExceptionFormatter
{
    /**
     * @api
     *
     * @template T of MatchedExceptionFormatter
     *
     * @phpstan-param ContainerInterface<class-string<T>,T> $formatterRegistry
     * @phpstan-param ?class-string<MatchedExceptionFormatter<Throwable,mixed>> $defaultFormatter
     *
     * @psalm-param ContainerInterface<class-string<MatchedExceptionFormatter>,MatchedExceptionFormatter> $formatterRegistry
     * @psalm-param ?class-string<MatchedExceptionFormatter> $defaultFormatter
     */
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
        private readonly ?string $defaultFormatter = null,
    ) {
    }

    public function format(MatchedException $matchedException): array
    {
        $catch = $matchedException->getCatchNode();

        $formatterId = $catch->getFormatterId() ?? $this->defaultFormatter;

        if (null === $formatterId) {
            throw new LogicException('No MatchedExceptionFormatter provided and no default one configured.');
        }

        return $this->getFormatter($formatterId)->format($matchedException);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     *
     * @return MatchedExceptionFormatter<Throwable,mixed>
     *
     * @internal
     *
     * @phpstan-param class-string<MatchedExceptionFormatter<Throwable,mixed>> $formatterId
     *
     * @psalm-param class-string<MatchedExceptionFormatter> $formatterId
     */
    public function getFormatter(string $formatterId): MatchedExceptionFormatter
    {
        if (!$this->formatterRegistry->has($formatterId)) {
            throw new UnregisteredExceptionFormatterException($formatterId);
        }

        /** @psalm-var MatchedExceptionFormatter<Throwable,mixed> */
        return $this->formatterRegistry->get($formatterId);
    }
}
