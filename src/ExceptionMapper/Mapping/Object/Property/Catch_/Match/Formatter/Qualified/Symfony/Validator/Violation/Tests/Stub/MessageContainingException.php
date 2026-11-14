<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Violation\Tests\Stub;

use RuntimeException;

final class MessageContainingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Exception message to be used');
    }
}
