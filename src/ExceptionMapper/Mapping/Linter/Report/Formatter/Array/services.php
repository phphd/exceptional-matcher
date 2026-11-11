<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\Array;

use PhPhD\ExceptionMapper\Mapping\Linter\Report\Formatter\LintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(LintReportFormatter::class.'<array>', ArrayLintReportFormatter::class)
        ->tag(LintReportFormatter::class, ['id' => 'array'])
    ;
};
