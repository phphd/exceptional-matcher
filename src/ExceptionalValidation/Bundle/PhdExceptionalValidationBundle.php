<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PhdExceptionalValidationBundle extends Bundle
{
    /** @override */
    protected function createContainerExtension(): PhdExceptionalValidationExtension
    {
        return new PhdExceptionalValidationExtension();
    }
}
