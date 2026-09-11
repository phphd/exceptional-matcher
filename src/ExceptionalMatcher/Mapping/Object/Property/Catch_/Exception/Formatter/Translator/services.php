<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Translator;

use Closure;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main\MainExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();
    $parameters = $configurator->parameters();

    $services
        ->set('phd_exceptional_matcher.translator', Closure::class) // removed if @translator is not found
        ->factory([Closure::class, 'fromCallable'])
        ->args([
            inline_service(SymfonyTranslator::class)
                ->args([
                    service('translator'),
                    param('phd_exceptional_matcher.translation_domain'),
                ]),
        ]);

    $parameters // removed if @translator is not found
        ->set('phd_exceptional_matcher.translation_domain', param('validator.translation_domain'));
};
