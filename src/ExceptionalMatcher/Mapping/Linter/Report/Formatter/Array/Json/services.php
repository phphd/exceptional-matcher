<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Array\Json;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\LintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(LintReportFormatter::class.'<json-string>', JsonLintReportFormatter::class)
        ->args([service(LintReportFormatter::class.'<array>')])
        ->tag(LintReportFormatter::class, ['id' => 'json'])
    ;
};
