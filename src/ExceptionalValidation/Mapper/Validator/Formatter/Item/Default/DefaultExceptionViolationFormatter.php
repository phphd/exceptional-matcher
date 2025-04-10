<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionViolationFormatter<Throwable>
 */
final class DefaultExceptionViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $translationDomain,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(CapturedException $capturedException): array
    {
        $exception = $capturedException->getException();
        $rule = $capturedException->getMatchedRule();

        $messageTemplate = $rule->getMessageTemplate() ?? $exception->getMessage();
        $root = $rule->getRoot();
        $propertyPath = $rule->getPropertyPath();
        $value = $rule->getValue();

        $message = $this->translator->trans($messageTemplate, domain: $this->translationDomain);

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
