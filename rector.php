<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\CallLike\AddNameToNullArgumentRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Class_\RemoveParentDelegatingClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\TypeDeclarationDocblocks\Rector\Property\MergePhpstanDocTagIntoNativeRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/upgrade'])
    ->withSets([PhdSetList::rector()->getPath()])
    ->withSkip([
        MergePhpstanDocTagIntoNativeRector::class, // spoils static analysis
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
            __DIR__.'/'.'src/ExceptionalMatcher/Port/Symfony/Validator/Middleware/Messenger/Exception/ExceptionalValidationFailedMessengerException.php',
        ],
        AddNameToNullArgumentRector::class => [
            __DIR__.'/'.'src/ExceptionalMatcher/Bundle/DependencyInjection/PhdExceptionalMatcherExtension.php',
        ],
        ...upgradeExclusions(),
        ...stubExclusions(),
    ])
    ->withSkip([
        // FIXME: process these rules, removing them by one:
        NewlineAfterStatementRector::class,
        NewlineBetweenClassLikeStmtsRector::class,
        ReadOnlyPropertyRector::class,
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
