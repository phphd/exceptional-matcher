<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Upgrade;

use function array_filter;
use function array_values;
use function version_compare;

/** @api */
final class ExceptionalValidationSetList
{
    private const VERSIONS = [
        '1.6' => __DIR__.'/1.6.php',
    ];

    public function __construct(
        /** @var array<string,string> */
        private readonly array $setList,
    ) {
    }

    public static function fromVersion(string $version): self
    {
        return new self(array_filter(
            self::VERSIONS,
            static fn (string $targetVersion): bool => version_compare($targetVersion, $version) > 0,
            ARRAY_FILTER_USE_KEY,
        ));
    }

    /** @return list<string> */
    public function getSetList(): array
    {
        return array_values($this->setList);
    }
}
