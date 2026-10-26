<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Try_',
        'PhPhD\ExceptionalValidation\Capture' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_',
        'PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle' => 'PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle',
        'PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension' => 'PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension',
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionalMatcher\Exception\MatchedException',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\ExceptionViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedExceptionFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\ExceptionalValidationFailedException',
        'PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware' => 'PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\Messenger\ExceptionalValidationMiddleware',
        'PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException' => 'PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException',
        'PhPhD\ExceptionalValidation\Mapper\ExceptionMapper' => 'PhPhD\ExceptionalMatcher\ExceptionMatcher',
    ]);
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException', 'getViolationList', 'getViolations'),
        new MethodCallRename('PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\ExceptionalValidationFailedException', 'getViolationList', 'getViolations'),
    ]);
};
