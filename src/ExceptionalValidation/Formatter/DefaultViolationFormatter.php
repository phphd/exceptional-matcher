<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @internal */
final class DefaultViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $translationDomain,
    ) {
    }

    public function formatViolation(CapturedException $capturedException): ConstraintViolationInterface
    {
        $rule = $capturedException->getMatchedRule();

        $messageTemplate = $rule->getMessageTemplate();
        $root = $rule->getRoot();
        $propertyPath = $rule->getPropertyPath();
        $value = $rule->getValue();

        $message = $this->translator->trans($messageTemplate, domain: $this->translationDomain);

        return new ConstraintViolation(
            $message,
            $messageTemplate,
            [],
            $root,
            $propertyPath->join('.'),
            $value,
        );
    }
}
