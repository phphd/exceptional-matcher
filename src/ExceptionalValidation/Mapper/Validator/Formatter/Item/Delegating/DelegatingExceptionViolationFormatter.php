<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionViolationFormatter<Throwable>
 */
final class DelegatingExceptionViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
    ) {
    }

    /** @throws ContainerExceptionInterface */
    public function format(CapturedException $capturedException): array
    {
        $matchedRule = $capturedException->getMatchedRule();
        $formatterId = $matchedRule->getFormatterId();

        if (!$this->formatterRegistry->has($formatterId)) {
            throw new LogicException('Violation formatter not found: '.$formatterId);
        }

        /** @var ExceptionViolationFormatter<Throwable> $exceptionFormatter */
        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        return $exceptionFormatter->format($capturedException);
    }
}
