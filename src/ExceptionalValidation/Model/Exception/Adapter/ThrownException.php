<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception\Adapter;

use Throwable;

/** @api */
interface ThrownException
{
    /** @return list<Throwable> */
    public function getExceptions(): array;

    public function getOriginalException(): Throwable;
}
