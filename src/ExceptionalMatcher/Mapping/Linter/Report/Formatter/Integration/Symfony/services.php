<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Integration\Symfony;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ConsoleLintReportFormatter::class, ConsoleLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'console'])
    ;
};
