<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\LintMappingUseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function class_exists;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists(Command::class)) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(LintExceptionalMatcherCommand::class, LintExceptionalMatcherCommand::class)
        ->public()
        ->args([service(LintMappingUseCase::class)])
        ->tag('console.command')
    ;
};
