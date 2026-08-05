<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter;

use PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\Console\ConsoleLintReportFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Report\Formatter\Json\JsonLintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ConsoleLintReportFormatter::class, ConsoleLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'txt'])
    ;

    $services
        ->set(JsonLintReportFormatter::class, JsonLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'json'])
    ;
};
