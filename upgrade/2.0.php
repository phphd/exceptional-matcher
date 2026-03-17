<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation' => 'PhPhD\ExceptionalValidation\Rule\Object\Try_',
        'PhPhD\ExceptionalValidation\Capture' => 'PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_',
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionalValidation\Rule\Exception\MatchedException',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionalValidation\Validator\Formatter\ExceptionViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList\ViolationListException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList\ViolationListExceptionFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionalValidation\Validator\Middleware\ExceptionalValidationFailedException',
        'PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware' => 'PhPhD\ExceptionalValidation\Validator\Middleware\Messenger\ExceptionalValidationMiddleware',
        'PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException' => 'PhPhD\ExceptionalValidation\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException',
    ]);
};
