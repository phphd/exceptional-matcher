<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionalValidationBundle extends Bundle
{
    /** @override */
    protected function createContainerExtension(): PhdExceptionalValidationExtension
    {
        return new PhdExceptionalValidationExtension(true);
    }
}
