<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements PropriatedExceptionFormatter<Throwable,mixed>
 */
final readonly class DelegatingPropriatedExceptionFormatter implements PropriatedExceptionFormatter
{
    /**
     * @api
     *
     * @template T of PropriatedExceptionFormatter
     *
     * @param ContainerInterface<class-string<T>,T> $formatterRegistry
     */
    public function __construct(
        private ContainerInterface $formatterRegistry,
    ) {
    }

    /** @template T of PropriatedExceptionFormatter */
    public function format(PropriatedException $propriatedException): array // @phpstan-ignore method.templateTypeNotInParameter
    {
        $matchedRule = $propriatedException->getMatchedRule();

        /** @var class-string<T> $formatterId */ // FIXME: use real type
        $formatterId = $matchedRule->getFormatterId();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new LogicException('Violation formatter not found: '.$formatterId);
        }

        /** @var T $exceptionFormatter */
        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        return $exceptionFormatter->format($propriatedException);
    }
}
