<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\Tests;

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
final class MainExceptionViolationFormatterBCBreakUnitTest extends TestCase
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

    public function testTranslatorIsRenamed(): void
    {
        $this->container->setDefinition('phd_exceptional_validation.translator', new Definition());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Translator service phd_exceptional_validation.translator is not available anymore. Please use phd_exceptional_matcher.translator instead.');

        $this->container->compile();
    }

    public function testTranslationDomainIsRenamed(): void
    {
        $this->container->setParameter('phd_exceptional_validation.translation_domain', 'validators');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parameter phd_exceptional_validation.translation_domain is not available anymore. Please use phd_exceptional_matcher.translation_domain instead.');

        $this->container->compile();
    }
}
