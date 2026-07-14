<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'PhPhD\ExceptionalValidation' => 'PhPhD\ExceptionalMatcher\Rule\Object\Try_',
        'PhPhD\ExceptionalValidation\Capture' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_',
        'PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle' => 'PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalMatcherBundle',
        'PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension' => 'PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension',
        'PhPhD\ExceptionalValidation\Model\Exception\CapturedException' => 'PhPhD\ExceptionalMatcher\Exception\MatchedException',
        'PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage' => 'PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueException' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ValueException',
        'PhPhD\ExceptionalValidation\Model\Condition\Value\ValueExceptionMatchCondition' => 'PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value\ExceptionValueMatchCondition',
        'PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListException' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException',
        'PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter' => 'PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\EmbeddedViolationListFormatter',
        'PhPhD\ExceptionalValidation\Handler\ExceptionHandler' => 'Symfony\Component\Messenger\Middleware\MiddlewareInterface', // implement middleware instead
        'PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\ExceptionalValidationFailedException',
        'PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\ExceptionalValidationMiddleware',
        'PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException' => 'PhPhD\ExceptionalMatcher\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException',
        'PhPhD\ExceptionalValidation\Mapper\ExceptionMapper' => 'PhPhD\ExceptionalMatcher\ExceptionMatcher',
    ]);
    /** @noinspection ClassConstantCanBeUsedInspection */
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException', 'getViolationList', 'getViolations'),
    ]);
};
