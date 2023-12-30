<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Tests;

use Nyholm\BundleTest\TestKernel;
use PhPhD\ExceptionalValidationBundle\PhdExceptionalValidationBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class TestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /** @param array<array-key,mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(PhdExceptionalValidationBundle::class);

        return $kernel;
    }
}
