<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionalMatcherBundle extends Bundle
{
    /** @override */
    protected function createContainerExtension(): PhdExceptionalMatcherExtension
    {
        return new PhdExceptionalMatcherExtension(true);
    }
}
