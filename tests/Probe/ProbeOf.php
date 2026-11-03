<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Probe;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ProbeOf
{
    public function __construct(
        public readonly UnitEnum $rule,
        public readonly ?string $label = null,
    ) {
    }
}
