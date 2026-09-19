<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

/** @internal */
final class SymfonyTranslator
{
    /** @api */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $translationDomain,
        private readonly ?string $locale = null,
    ) {
    }

    /** @param array<array-key,mixed> $parameters */
    public function __invoke(string $messageTemplate, array $parameters = []): string
    {
        return $this->translator->trans($messageTemplate, $parameters, domain: $this->translationDomain, locale: $this->locale);
    }
}
