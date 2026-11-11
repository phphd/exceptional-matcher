<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Class\Path;

use PhPhD\ExceptionMapper\Mapping\Linter\Class\Path\Discovery\ClassNameDiscovery;
use PhPhD\ExceptionMapper\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<path-string,'.LintReport::class.'>', ClassPathMappingLinter::class)
        ->arg('$classNameDiscovery', inline_service(ClassNameDiscovery::class))
        ->arg('$classMappingLinter', service(MappingLinter::class.'<class-string,'.LintReport::class.'>'))
        ->tag(MappingLinter::class, ['id' => 'path-string'])
    ;
};
