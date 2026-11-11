<?php

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 *
 * @template TResult of mixed
 *
 * @template TFormatter of ExceptionMatchFormatter<TException,TResult>
 *
 * @implements ExceptionMatchFormatter<TException,TResult>
 */
final class QualifiedExceptionMatchFormatter implements ExceptionMatchFormatter
{
    /**
     * @api
     *
     * @param ContainerInterface<class-string<TFormatter>,TFormatter> $formatterRegistry
     * @param ?class-string<TFormatter> $defaultFormatter
     */
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
        private readonly ?string $defaultFormatter = null,
    ) {
    }

    public function format(ExceptionMatch $exceptionMatch): array
    {
        $catch = $exceptionMatch->getNode();

        /** @var ?class-string<TFormatter> $formatterId */
        $formatterId = $catch->getFormatterId() ?? $this->defaultFormatter;

        if (null === $formatterId) {
            throw new LogicException('No ExceptionMatchFormatter provided and no default one configured.');
        }

        return $this->getFormatter($formatterId)->format($exceptionMatch);
    }

    /**
     * @internal
     *
     * @param class-string<TFormatter> $formatterId
     *
     * @return TFormatter
     */
    public function getFormatter(string $formatterId): ExceptionMatchFormatter
    {
        if (!$this->formatterRegistry->has($formatterId)) {
            throw new UnregisteredExceptionFormatterException($formatterId);
        }

        /** @psalm-suppress ArgumentTypeCoercion - Psalm degrades class-string<TFormatter> to class-string */
        return $this->formatterRegistry->get($formatterId);
    }
}
