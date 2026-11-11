<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation' => 'PhPhD\ExceptionMapper\Mapping\Object\Try_',
        'PhPhD\ExceptionalValidation\Capture' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_',
        'PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle' => 'PhPhD\ExceptionMapper\Bundle\PhdExceptionMapperBundle',
        'PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension' => 'PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension',
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatch',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionReciprocal',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedExceptionFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Exception\ExceptionalValidationFailedException',
        'PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware' => 'PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Messenger\ExceptionalValidationMiddleware',
        'PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException' => 'PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Messenger\Exception\ExceptionalValidationFailedMessengerException',
        'PhPhD\ExceptionalValidation\Mapper\ExceptionMapper' => 'PhPhD\ExceptionMapper\ExceptionMapper',
    ]);
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Symfony\Validator\Embedded\ViolationsEmbeddedException', 'getViolationList', 'getViolations'),
        new MethodCallRename('PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Exception\ExceptionalValidationFailedException', 'getViolationList', 'getViolations'),
    ]);
};
