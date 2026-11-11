<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Main;

use Closure;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMatchFormatter<Throwable,ConstraintViolationInterface>
 */
final class MainExceptionViolationFormatter implements ExceptionMatchFormatter
{
    /** @var Closure(string):string */
    private readonly Closure $translate;

    /**
     * @api
     *
     * @param ?Closure(string):string $translate
     */
    public function __construct(?Closure $translate = null)
    {
        $this->translate = $translate ?? static fn(string $messageTemplate): string => $messageTemplate;
    }

    /** @return array{ConstraintViolation} */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        $exception = $exceptionMatch->getException();
        $node = $exceptionMatch->getNode();

        $messageTemplate = $node->getMessageTemplate() ?? $exception->getMessage();
        $message = ($this->translate)($messageTemplate);
        $root = $node->getRootObject();
        $propertyPath = $node->getPropertyPath();
        $value = $node->getValue();

        return [
            new ConstraintViolation(
                $message,
                $messageTemplate,
                [],
                $root,
                $propertyPath->join('.'),
                $value,
            ),
        ];
    }
}
