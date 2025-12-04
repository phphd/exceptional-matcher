<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements PropriatedExceptionFormatter<Throwable>
 */
final readonly class DelegatingPropriatedExceptionFormatter implements PropriatedExceptionFormatter
{
    /**
     * @api
     *
     * @template T of Throwable
     *
     * @phpstan-param ContainerInterface<class-string<PropriatedExceptionFormatter<T>>,PropriatedExceptionFormatter<T>> $formatterRegistry
     *
     * @psalm-param ContainerInterface<class-string<PropriatedExceptionFormatter>,PropriatedExceptionFormatter> $formatterRegistry
     */
    public function __construct(
        private ContainerInterface $formatterRegistry,
    ) {
    }

    /**
     * @template T of Throwable
     *
     * @param PropriatedException<T> $propriatedException
     *
     * @psalm-suppress MoreSpecificImplementedParamType, LessSpecificImplementedReturnType
     */
    public function format(PropriatedException $propriatedException): array
    {
        $matchedRule = $propriatedException->getMatchedRule();

        /** @var class-string<PropriatedExceptionFormatter<T>> $formatterId */ // FIXME: use real type
        $formatterId = $matchedRule->getFormatterId();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new LogicException('Violation formatter not found: '.$formatterId);
        }

        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        /** @psalm-var PropriatedExceptionFormatter<T> $exceptionFormatter */
        return $exceptionFormatter->format($propriatedException);
    }
}
