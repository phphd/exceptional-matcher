<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload;

use function array_map;
use function glob;
use function sprintf;
use function str_repeat;

/** @api */
final class ConstantsClassLoader
{
    /** @param list<class-string> $classNames */
    public static function loadClassNames(array $classNames): void
    {
        array_map(class_exists(...), $classNames);
    }

    /** @codeCoverageIgnore */
    public static function loadFiles(string $basePath, int $depth = 7): void
    {
        $nestingPattern = str_repeat('{,*/}', $depth);

        /** @var list<string> $glob */
        $glob = glob(
            $basePath.sprintf('/%s*MatchConditionFactory.php', $nestingPattern),
            GLOB_BRACE | GLOB_NOSORT,
        );

        foreach ($glob as $file) {
            /** @psalm-suppress UnresolvableInclude */

            require_once $file;
        }
    }
}
