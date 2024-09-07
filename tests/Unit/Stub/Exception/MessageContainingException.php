<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

use RuntimeException;

final class MessageContainingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This is the message to be used');
    }
}
