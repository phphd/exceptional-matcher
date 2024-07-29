<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Tests;

use Nyholm\BundleTest\TestKernel;
use PhPhD\ExceptionalValidationBundle\PhdExceptionalValidationBundle;
use PhPhD\ExceptionalValidationBundle\Tests\Compiler\TestServicesCompilerPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class TestCase extends KernelTestCase
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
        // priority 105 is primarily necessary for interface autoconfiguration
        $kernel->addTestCompilerPass(new TestServicesCompilerPass(), priority: 105);

        return $kernel;
    }
}
