<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Probe;

use Attribute;
use UnitEnum;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final class ProbeOf
{
    public function __construct(
        public readonly UnitEnum $rule,
        public readonly ?string $label = null,
    ) {
    }
}
