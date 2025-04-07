<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.match_condition_factory', CaptureMatchConditionFactory::class)
        ->factory([CaptureMatchConditionFactory::class, 'create'])
        ->args([
            tagged_locator('exceptional_validation.match_condition_factory', 'id'),
        ])
    ;
};
