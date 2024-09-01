<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\Tests;

use Nyholm\BundleTest\TestKernel;
use PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle;
use PhPhD\ExceptionalValidation\Bundle\Tests\Compiler\TestServicesCompilerPass;
use PhPhD\ExceptionToolkit\Bundle\PhdExceptionToolkitBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BundleTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        $container = self::getContainer();

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => $id);
        $container->set('translator', $translator);
    }

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
        $kernel->addTestBundle(PhdExceptionToolkitBundle::class);
        // priority 105 is primarily necessary for interface autoconfiguration to work properly
        $kernel->addTestCompilerPass(new TestServicesCompilerPass(), priority: 105);

        return $kernel;
    }
}
