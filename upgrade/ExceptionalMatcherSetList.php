<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Upgrade;

use Composer\InstalledVersions;
use LogicException;

use function array_filter;
use function array_values;
use function sprintf;
use function version_compare;

/** @api */
final class ExceptionalMatcherSetList
{
    private const VERSIONS = [
        '2.0' => __DIR__.'/2.0.php',
    ];

    public function __construct(
        /** @var array<string,string> */
        private readonly array $setList,
    ) {
        $rectorVersion = InstalledVersions::getVersion('rector/rector') ?? '0';

        if (version_compare($rectorVersion, '2.0', '<')
            || version_compare($rectorVersion, '3.0', '>=')) {
            throw new LogicException(sprintf('Version %s of rector is not supported by this version of library', $rectorVersion));
        }
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
