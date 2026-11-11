<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port;

use LogicException;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension
 *
 * @internal
 */
final class MainExceptionMapperBCBreakUnitTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $this->container = $container;
    }

    public function testExceptionUnwrapperIsRenamed(): void
    {
        $this->container->setDefinition('phd_exceptional_validation.exception_unwrapper', new Definition());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Service phd_exceptional_validation.exception_unwrapper is not available anymore. Please use phd_exception_mapper.exception_unwrapper instead.');

        $this->container->compile();
    }
}
