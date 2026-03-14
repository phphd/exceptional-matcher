<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\Main\Tests\Stub;

use RuntimeException;

final class MessageContainingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Exception message to be used');
    }
}
