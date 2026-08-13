<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Array\ArrayLintReportFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Formatter\Array\Json\JsonLintReportFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(LintReportFormatter::class.'<json-string>', JsonLintReportFormatter::class)
        ->args([inline_service(ArrayLintReportFormatter::class)])
        ->tag(LintReportFormatter::class, ['id' => 'json']);
};
