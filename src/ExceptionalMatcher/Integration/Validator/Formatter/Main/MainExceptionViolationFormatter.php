<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main;

use Closure;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedException;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionViolationFormatter<Throwable>
 */
final class MainExceptionViolationFormatter implements ExceptionViolationFormatter
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
        $this->translate = $translate ?? static fn (string $messageTemplate): string => $messageTemplate;
    }

    /** @return array{ConstraintViolation} */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();
        $node = $matchedException->getCatchNode();

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
