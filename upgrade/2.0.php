<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation' => 'PhPhD\ExceptionalMatcher\Rule\Object\Try_',
        'PhPhD\ExceptionalValidation\Capture' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_',
        'PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle' => 'PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle',
        'PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension' => 'PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension',
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionalMatcher\Rule\Exception\MatchedException',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionalMatcher\Rule\Exception\ExceptionReciprocal',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListExceptionFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\ExceptionalValidationFailedException',
        'PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\ExceptionalValidationMiddleware',
        'PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException',
        'PhPhD\ExceptionalValidation\Mapper\ExceptionMapper' => 'PhPhD\ExceptionalMatcher\ExceptionMatcher',
    ]);
};
