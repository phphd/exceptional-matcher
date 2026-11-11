<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Trait;

use Closure;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @mixin TestCase
 *
 * @phpstan-require-extends TestCase
 * @psalm-require-extends TestCase
 */
trait CatchException // @phpstan-ignore requireExtends.duplicate
{
    /**
     * @template T of Throwable
     *
     * @param Closure(): mixed $act
     * @param class-string<T> $exceptionClass
     *
     * @return T
     *
     * @noinspection PhpDocMissingThrowsInspection,PhpUnhandledExceptionInspection
     */
    private static function catchException(Closure $act, string $exceptionClass): Throwable
    {
        try {
            $act();
        } catch (Throwable $exception) {
            if (!$exception instanceof $exceptionClass) {
                throw $exception;
            }

            return $exception;
        }

        self::fail('The exception must be thrown.');
    }
}
