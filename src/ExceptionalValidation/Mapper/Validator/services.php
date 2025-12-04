<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use Closure;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\PropriatedExceptionListFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
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
        ->set(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionViolationListMapper::class)
        ->public()
        ->args([
            service(ExceptionMapper::class.'<non-empty-list<'.PropriatedException::class.'<Throwable>>>'),
            service(PropriatedExceptionListFormatter::class.'<'.ConstraintViolationListInterface::class.'>'),
        ])
        ->lazy($lazy(ExceptionMapper::class))
    ;
};
