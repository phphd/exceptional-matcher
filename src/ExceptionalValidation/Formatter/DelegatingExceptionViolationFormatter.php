<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

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

        /** @var ExceptionViolationFormatter $exceptionFormatter */
        $exceptionFormatter = $this->formatterRegistry->get($matchedRule->getFormatterId());

        return $exceptionFormatter->format($capturedException);
    }
}
