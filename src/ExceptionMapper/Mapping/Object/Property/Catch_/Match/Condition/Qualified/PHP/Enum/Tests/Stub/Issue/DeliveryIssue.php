<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub\Issue;

use ValueError;

enum DeliveryIssue
{
    case LOST;
    case DAMAGED;
    case LATE;

    /** @api */
    public static function from(string $value): self
    {
        return match ($value) {
            self::LOST->name => self::LOST,
            self::DAMAGED->name => self::DAMAGED,
            self::LATE->name => self::LATE,
            default => throw new ValueError('Invalid value: '.$value),
        };
    }
}
