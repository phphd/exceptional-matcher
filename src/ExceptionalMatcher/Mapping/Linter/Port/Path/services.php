<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Path;

use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Path\Discovery\ClassNameDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<'.'path-string,'.LintReport::class.'>', ClassPathBasedLinter::class)
        ->args([
            inline_service(ClassNameDiscovery::class),
            service(MappingLinter::class.'<'.'class-string,'.LintReport::class.'>'),
        ])
        ->tag(MappingLinter::class, ['id' => 'paths']);
};
