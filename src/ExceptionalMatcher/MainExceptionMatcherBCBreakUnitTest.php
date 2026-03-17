<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension
 *
 * @internal
 */
final class MainExceptionMatcherBCBreakUnitTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $this->container = $container;
    }

    public function testExceptionUnwrapperIsRenamed(): void
    {
        $this->container->setDefinition('phd_exceptional_validation.exception_unwrapper', new Definition());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Service phd_exceptional_validation.exception_unwrapper is not available anymore. Please use phd_exceptional_matcher.exception_unwrapper instead.');

        $this->container->compile();
    }
}
