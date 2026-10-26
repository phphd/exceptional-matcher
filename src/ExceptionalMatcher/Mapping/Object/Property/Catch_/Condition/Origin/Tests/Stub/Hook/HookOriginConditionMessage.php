<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\Hook;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use const PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\embedded_violations;

#[Try_]
final class HookOriginConditionMessage
{
        public function __construct(
        #[Catch_(ValidationFailedException::class, from: [ProductHookedEntity::class, '$title::set'], format: embedded_violations)]
        public string $title,
    ) {
    }
}
