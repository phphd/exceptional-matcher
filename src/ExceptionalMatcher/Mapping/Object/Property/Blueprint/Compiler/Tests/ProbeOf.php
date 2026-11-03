<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ProbeOf
{
    public function __construct(
        public readonly InclusionRule $rule,
        public readonly ?string $label = null,
    ) {
    }
}
