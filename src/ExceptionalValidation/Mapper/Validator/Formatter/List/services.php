<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use Closure;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services = $containerConfigurator->services();

    $services
        ->set(
            PropriatedExceptionListFormatter::class.'<'.ConstraintViolationListInterface::class.'>',
            PropriatedExceptionListToViolationListFormatter::class,
        )->args([
            service(ExceptionViolationFormatter::class),
        ])
        ->lazy($lazy(PropriatedExceptionListFormatter::class))
    ;
};
