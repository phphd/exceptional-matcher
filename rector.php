<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToBooleanArgumentRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToNullArgumentRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveDuplicatedReturnSelfDocblockRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveParentDelegatingClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector;
use Rector\Symfony\Configs\Rector\Closure\MergeServiceNameTypeRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSets([PhdSetList::rector()->getPath()])
    ->withSkip([
        ArrayToFirstClassCallableRector::class => [
            __DIR__.'/src/*/services.php',
            __DIR__.'/src/*/*CompilerPass.php',
        ],
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__.'/'.'src/ExceptionalMatcher/Mapping/Object/Property/Catch_/Exception/ExceptionReciprocal.php',
        ],
        IssetOnPropertyObjectToPropertyExistsRector::class => [
            __DIR__.'/'.'src/ExceptionalMatcher/Mapping/Object/Property/Catch_/Condition/Origin/ExceptionOriginMatchCondition.php',
        ],
        RemoveParentDelegatingClassMethodRector::class => [
            __DIR__.'/'.'src/ExceptionalMatcher/Integration/Validator/Middleware/Messenger/ExceptionalValidationFailedMessengerException.php',
        ],
        AddNameToNullArgumentRector::class => [
            __DIR__.'/'.'src/ExceptionalMatcher/Bundle/DependencyInjection/PhdExceptionalMatcherExtension.php',
        ],
        ...upgradeExclusions(),
        ...stubExclusions(),
    ]);

function upgradeExclusions(): array
{
    return array_fill_keys([
        StringClassNameToClassConstantRector::class,
        ReduceAlwaysFalseIfOrRector::class,
    ], [__DIR__.'/upgrade']);
}

function stubExclusions(): array
{
    return array_fill_keys([
        RemoveUnusedPrivatePropertyRector::class,
        RemoveUnusedPromotedPropertyRector::class,
        ReadOnlyPropertyRector::class,
        ExplicitAttributeNamedArgsRector::class,
    ], [__DIR__.'/*/Stub/*']);
}
