<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionalValidation\Rule\Exception\CapturedException',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter' => 'PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\ExceptionListViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListExceptionFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionalValidation\Middleware\ExceptionalValidationFailedException',
    ]);
};
