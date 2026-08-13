<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Integration\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\Discovery\ClassNameDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function class_exists;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists(Command::class)) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(LintExceptionalMatcherCommand::class, LintExceptionalMatcherCommand::class)
        ->public()
        ->args([
            service(MappingLinter::class),
            inline_service(ClassNameDiscovery::class),
            tagged_locator(LintReportFormatter::class, 'id'),
        ])
        ->tag('console.command')
    ;
};
