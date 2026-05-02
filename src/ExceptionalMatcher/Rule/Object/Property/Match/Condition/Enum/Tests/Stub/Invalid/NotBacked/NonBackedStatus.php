<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\NotBacked;

use ValueError;

enum NonBackedStatus
{
    case ANY;

    /** @api */
    public static function from(string $value): self
    {
        return match ($value) {
            self::ANY->name => self::ANY,
            default => throw new ValueError('Invalid value: '.$value),
        };
    }
}
