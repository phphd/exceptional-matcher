<?php

declare(strict_types=1);

namespace Symfony\Component\Uid\Exception;

if (!class_exists(InvalidArgumentException::class)) {
    /** @see \Symfony\Component\Uid\Exception\InvalidArgumentException */
    final class InvalidArgumentException extends \InvalidArgumentException
    {
        /** @var mixed */
        public $invalidValue;
    }
}
