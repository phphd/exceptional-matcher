<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Command;

use PhPhD\ExceptionalMatcher\Integration\Linter\Discovery\ClassMapDiscovery;
use PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function class_exists;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
            inline_service(ClassMapDiscovery::class),
        ])
        ->tag('console.command')
    ;
};
