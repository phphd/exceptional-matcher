<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Throwable;

/**
 * @api
 *
 * @template-covariant T of mixed
 */
interface PropriatedExceptionListFormatter
{
    /**
     * @param non-empty-list<PropriatedException<Throwable>> $propriatedExceptionList
     *
     * @return ?T
     */
    public function format(array $propriatedExceptionList): mixed;
}
