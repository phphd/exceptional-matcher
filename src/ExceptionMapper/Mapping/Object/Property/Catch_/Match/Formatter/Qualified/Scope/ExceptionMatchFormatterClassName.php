<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Throwable;
use Webmozart\Assert\Assert;

use function preg_match;
use function preg_quote;
use function sprintf;
use function str_starts_with;

/** @internal */
final class ExceptionMatchFormatterClassName
{
    private const GENERIC_ID_FORMAT = ExceptionMatchFormatter::class.'<TException,TResult>';

    /** @var array{TException:class-string<Throwable>,TResult:string} */
    private readonly array $genericArguments;

    public function __construct(
        private readonly string $className,
    ) {
        Assert::true(self::isFormatterClassName($className));

        $this->genericArguments = $this->parseGenericArguments();
    }

    public static function isFormatterClassName(string $id): bool
    {
        return str_starts_with($id, ExceptionMatchFormatter::class.'<') || ExceptionMatchFormatter::class === $id;
    }

    public function getResultType(): string
    {
        return $this->genericArguments['TResult'];
    }

    /** @return array{TException:class-string<Throwable>,TResult:string} */
    private function parseGenericArguments(): array
    {
        $typeNamePattern = '[\w\\\\]+';
        $genericIdPattern = '/^'.preg_quote(ExceptionMatchFormatter::class, '/').'<(?<TException>'.$typeNamePattern.'),(?<TResult>'.$typeNamePattern.')>$/';

        if (1 !== preg_match($genericIdPattern, $this->className, $typeArguments)) {
            throw new LogicException(sprintf('Formatter id "%s" must match "%s".', $this->className, self::GENERIC_ID_FORMAT));
        }

        /** @var array{TException:class-string<Throwable>,TResult:string} $typeArguments */
        return $typeArguments;
    }
}
