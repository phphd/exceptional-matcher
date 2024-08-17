<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use LogicException;
use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/** @internal */
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

        /** @var ExceptionViolationFormatter $exceptionFormatter */
        $exceptionFormatter = $this->formatterRegistry->get($formatterId);

        return $exceptionFormatter->format($capturedException);
    }
}
