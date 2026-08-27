<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port;

use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(LintMappingUseCase::class, LintMappingUseCase::class)
        ->args([
            tagged_locator(MappingLinter::class, 'id'),
            tagged_locator(LintReportFormatter::class, 'id'),
        ]);
};
