<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Main;

use Closure;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Contracts\Translation\TranslatorInterface;
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

    /** @api */
    public static function translator(TranslatorInterface $translator, string $translationDomain): Closure
    {
        return static fn (string $messageTemplate): string => $translator->trans($messageTemplate, domain: $translationDomain);
    }

    /** @return array{ConstraintViolation} */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();
        $rule = $matchedException->getRule();

        $messageTemplate = $rule->getMessageTemplate() ?? $exception->getMessage();
        $message = ($this->translate)($messageTemplate);
        $root = $rule->getRootObject();
        $propertyPath = $rule->getPropertyPath();
        $value = $rule->getValue();

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
