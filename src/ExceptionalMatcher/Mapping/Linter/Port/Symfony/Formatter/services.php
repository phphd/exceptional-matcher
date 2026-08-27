<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Port\Symfony\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists(Command::class)) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(ConsoleLintReportFormatter::class, ConsoleLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'console'])
    ;
};
