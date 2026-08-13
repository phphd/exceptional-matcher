<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter\Array\ArrayLintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\Formatter\Console\ConsoleLintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ConsoleLintReportFormatter::class, ConsoleLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'txt'])
    ;

    $services
        ->set(ArrayLintReportFormatter::class, ArrayLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'json'])
    ;
};
