<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\Tests\Stub;

/** @internal */
final class SpyClassLoader
{
    /** @var list<string> */
    private array $requestedClassNames = [];

    public function __invoke(string $className): void
    {
        $this->requestedClassNames[] = $className;
    }

    /** @return list<string> */
    public function flushRequestedClassNames(): array
    {
        try {
            return $this->requestedClassNames;
        } finally {
            $this->requestedClassNames = [];
        }
    }
}
