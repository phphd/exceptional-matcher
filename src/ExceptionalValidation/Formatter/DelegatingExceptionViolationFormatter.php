<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/** @internal */
final class DelegatingExceptionViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly ContainerInterface $formatterRegistry,
    ) {
    }

    /** @throws ContainerExceptionInterface */
    public function formatViolation(CapturedException $capturedException): ConstraintViolationInterface
    {
        $matchedRule = $capturedException->getMatchedRule();

        /** @var ExceptionViolationFormatter $formatter */
        $formatter = $this->formatterRegistry->get($matchedRule->getFormatterId());

        return $formatter->formatViolation($capturedException);
    }
}
